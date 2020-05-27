<?php

namespace VersoBit\ResourceTickets\XFRM\Service\ResourceUpdate;

class Create extends XFCP_Create
{
    protected function _save()
    {
        $update = parent::_save();

        // If resource awaiting approval
        if($update->message_state == 'moderated'){
            if($update->Resource->Ticket) {
                // Create new reply if resource already has ticket
                $this->createTicketReplyForResourceUpdate($update);
            }else{
                // Create new ticket if one doesn't yet exist
                $this->createTicketForResource($update->Resource, $update);
            }
        }

        return $update;
    }

    protected function createTicketReplyForResourceUpdate($update)
    {
        \XF::asVisitor($update->Resource->User, function() use ($update)
        {
            /** @var Replier $ticketReplyService */
            $ticketReplyService = $this->app->service('NF\Tickets:Ticket\Replier', $update->Resource->Ticket);
            $ticketReplyService->logIp(false);
            $ticketReplyService->setMessage("Update [B](".$update->title.")[/B] submitted.", false);
            $ticketReplyService->getTicket()->status_id = \XF::options()->nfTicketsDefaultStatus;
            $ticketReplyService->getTicket()->prefix_id = \XF::options()->versobitResourceTicketsAwaitingApprovalPrefixId;
            $ticketReplyService->save();
            $ticketReplyService->sendNotifications();
        });
    }

    protected function createTicketForResource($resource, $update)
    {
        // Get ticket category instance
        $ticketCategory = \XF::finder('NF\Tickets:Category')->where('ticket_category_id', \XF::options()->versobitResourceTicketsCategoryId)->fetchOne();

        // Create new ticket
        \XF::asVisitor($resource->User, function() use ($ticketCategory, $resource, $update)
        {
            /** @var Creator $ticketCreateService */
            $ticketCreateService = $this->app->service('NF\Tickets:Ticket\Creator', $ticketCategory);
            $ticketCreateService->setIsAutomated();
            $ticketCreateService->setContent($resource->title, "Update [B](".$update->title.")[/B] submitted.", false);
            $ticketCreateService->setPrefix(\XF::options()->versobitResourceTicketsAwaitingApprovalPrefixId);
            $ticketCreateService->save();
            $ticketCreateService->sendNotifications();

            // Set resource's ticket ID
            // TODO: work out way of moving this out of 'asVisitor' by passing $ticketCreateService
            $resource->fastUpdate('ticket_id', $ticketCreateService->getTicket()->ticket_id);
        });
    }
}