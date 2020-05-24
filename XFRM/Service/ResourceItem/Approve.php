<?php

namespace VersoBit\ResourceTickets\XFRM\Service\ResourceItem;

class Approve extends XFCP_Approve
{
    protected function onApprove()
    {
        parent::onApprove();

        $resource = $this->resource;

        if($resource->Ticket){
            $this->createTicketReplyForResource($resource);
        }else{
            $this->createTicketForResource($resource);
        }
    }

    protected function createTicketReplyForResource($resource)
    {
        // Get reply user instance
        $replyUser = \XF::visitor();

        // Create new reply if resource already has ticket
        \XF::asVisitor($replyUser, function() use ($resource)
        {
            /** @var Replier $ticketReplyService */
            $ticketReplyService = $this->app->service('NF\Tickets:Ticket\Replier', $resource->Ticket);
            $ticketReplyService->logIp(false);
            $ticketReplyService->setMessage("Your submission ".$resource->title." has been approved and is now available publicly for users to download! Thanks for sharing your work with the community.", false);
            $ticketReplyService->getTicket()->status_id = \XF::options()->nftResolvedStatus;
            $ticketReplyService->save();
            $ticketReplyService->sendNotifications();
        });
    }

    protected function createTicketForResource($resource)
    {
        // Get ticket category instance
        $ticketCategory = \XF::finder('NF\Tickets:Category')->where('ticket_category_id', \XF::options()->versobitResourceTicketsCategoryId)->fetchOne();
        // Get reply user instance
        $replyUser = \XF::visitor();

        // Create new ticket
        \XF::asVisitor($replyUser, function() use ($resource, $ticketCategory)
        {
            /** @var Creator $ticketCreateService */
            $ticketCreateService = $this->app->service('NF\Tickets:Ticket\Creator', $ticketCategory);
            $ticketCreateService->setIsAutomated();
            $ticketCreateService->createForMember($resource->User);
            $ticketCreateService->setContent($resource->title, "Your submission ".$resource->title." has been approved and is now available publicly for users to download! Thanks for sharing your work with the community.", false);
            $ticketReplyService->getTicket()->status_id = \XF::options()->nftResolvedStatus;
            $ticketCreateService->save();
            $ticketCreateService->sendNotifications();

            // Set resource's ticket ID
            // TODO: work out way of moving this out of 'asVisitor' by passing $ticketCreateService
            $resource->fastUpdate('ticket_id', $ticketCreateService->getTicket()->ticket_id);
        });
    }
}