<?php

namespace VersoBit\ResourceTickets\XFRM\Service\ResourceItem;

class Reassign extends XFCP_Reassign
{
    public function reassignTo(\XF\Entity\User $newUser)
    {
        $reassigned = parent::reassignTo($newUser);

        $resource = $this->resource;
        $oldTicket = $resource->Ticket;

        if($resource->Ticket){
            // Create new ticket with new user as starter
            $this->createTicketForResource($resource, $oldTicket);

            // Reply to old ticket and set resolved status
            $this->closeOldTicket($resource, $oldTicket);
        }

        return $reassigned;
    }

    protected function createTicketForResource($resource, $oldTicket)
    {
        // Get ticket category instance
        $ticketCategory = \XF::finder('NF\Tickets:Category')->where('ticket_category_id', \XF::options()->versobitResourceTicketsCategoryId)->fetchOne();
        // Get reply user instance
        $replyUser = \XF::visitor();

        // Create new ticket
        \XF::asVisitor($replyUser, function() use ($ticketCategory, $resource, $oldTicket)
        {
            /** @var Creator $ticketCreateService */
            $ticketCreateService = $this->app->service('NF\Tickets:Ticket\Creator', $ticketCategory);
            $ticketCreateService->createForMember($resource->User);
            $ticketCreateService->logIp(false);
            $ticketCreateService->setContent($resource->title, "[B]".$resource->title."[/B]'s author changed from [USER=".$oldTicket->User->user_id."]@".$oldTicket->User->username."[/USER] to [USER=".$resource->User->user_id."]@".$resource->User->username."[/USER].", false);
            $ticketCreateService->setStatus($oldTicket->status_id);
            $ticketCreateService->setPrefix($oldTicket->prefix_id);
            $ticketCreateService->save();
            $ticketCreateService->sendNotifications();

            // Set resource's ticket ID
            // TODO: work out way of moving this out of 'asVisitor' by passing $ticketCreateService
            $resource->fastUpdate('ticket_id', $ticketCreateService->getTicket()->ticket_id);
        });
    }

    protected function closeOldTicket($resource, $oldTicket)
    {
        // Get reply user instance
        $replyUser = \XF::visitor();

        \XF::asVisitor($replyUser, function() use ($resource, $oldTicket)
        {
            /** @var Replier $ticketReplyService */
            $ticketReplyService = $this->app->service('NF\Tickets:Ticket\Replier', $oldTicket);
            $ticketReplyService->setMessage("[B]" . $resource->title . "[/B]'s author changed from [USER=" . $oldTicket->User->user_id . "]@" . $oldTicket->User->username . "[/USER] to [USER=" . $resource->User->user_id . "]@" . $resource->User->username . "[/USER].", false);
            $ticketReplyService->getTicket()->status_id = \XF::options()->nftResolvedStatus;
            $ticketReplyService->save();
            $ticketReplyService->sendNotifications();
        });
    }
}