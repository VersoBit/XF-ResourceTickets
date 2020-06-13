<?php

namespace VersoBit\ResourceTickets\XFRM\Service\ResourceUpdate;

class Approve extends XFCP_Approve
{
    protected function onApprove()
    {
        parent::onApprove();

        $update = $this->update;

        if($update->Resource->Ticket){
            $this->createTicketReplyForResourceUpdate($update);
        }else{
            $this->createTicketForResourceUpdate($update);
        }
    }

    protected function createTicketReplyForResourceUpdate($update)
    {
        // Get reply user instance
        $replyUser = \XF::visitor();

        // Create new reply if resource already has ticket
        \XF::asVisitor($replyUser, function() use ($update)
        {
            /** @var Replier $ticketReplyService */
            $ticketReplyService = $this->app->service('NF\Tickets:Ticket\Replier', $update->Resource->Ticket);
            $ticketReplyService->logIp(false);
            $ticketReplyService->setMessage("Your update [B]".$update->title."[/B] has been approved and is now available publicly for users to download! Thanks for sharing your work with the community.", false);
            $ticketReplyService->getTicket()->status_id = \XF::options()->nftResolvedStatus;
            $ticketReplyService->getTicket()->prefix_id = \XF::options()->versobitResourceTicketsAcceptedPrefixId;
            $ticketReplyService->save();
            $ticketReplyService->sendNotifications();
        });
    }

    protected function createTicketForResourceUpdate($update)
    {
        // Get ticket category instance
        $ticketCategory = \XF::finder('NF\Tickets:Category')->where('ticket_category_id', \XF::options()->versobitResourceTicketsCategoryId)->fetchOne();
        // Get reply user instance
        $replyUser = \XF::visitor();

        // Create new ticket
        \XF::asVisitor($replyUser, function() use ($update, $ticketCategory)
        {
            /** @var Creator $ticketCreateService */
            $ticketCreateService = $this->app->service('NF\Tickets:Ticket\Creator', $ticketCategory);
            $ticketCreateService->createForMember($update->Resource->User);
            $ticketCreateService->setContent($update->title, "Your update [B]".$update->title."[/B] has been approved and is now available publicly for users to download! Thanks for sharing your work with the community.", false);
            $ticketCreateService->setStatus(\XF::options()->nftResolvedStatus);
            $ticketCreateService->setPrefix(\XF::options()->versobitResourceTicketsAcceptedPrefixId);
            $ticketCreateService->save();
            $ticketCreateService->sendNotifications();

            // Set resource's ticket ID
            // TODO: work out way of moving this out of 'asVisitor' by passing $ticketCreateService
            $update->Resource->fastUpdate('ticket_id', $ticketCreateService->getTicket()->ticket_id);
        });
    }
}