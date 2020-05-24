<?php

namespace VersoBit\ResourceTickets\XFRM\Service\ResourceItem;

class Create extends XFCP_Create
{
    protected function _save()
    {
        $resource = parent::_save();

        $this->createTicketForResource($resource);

        return $resource;
    }

    protected function createTicketForResource($resource)
    {
        // Get ticket category instance
        $ticketCategory = \XF::finder('NF\Tickets:Category')->where('ticket_category_id', \XF::options()->versobitResourceTicketsCategoryId)->fetchOne();

        // Create new ticket
        \XF::asVisitor($resource->User, function() use ($ticketCategory, $resource)
        {
            /** @var Creator $ticketCreateService */
            $ticketCreateService = $this->app->service('NF\Tickets:Ticket\Creator', $ticketCategory);
            $ticketCreateService->setIsAutomated();
            $ticketCreateService->logIp(false);
            $ticketCreateService->setContent($resource->title, $resource->title." submitted.", false);
            $ticketCreateService->save();
            $ticketCreateService->sendNotifications();

            // Set resource's ticket ID
            // TODO: work out way of moving this out of 'asVisitor' by passing $ticketCreateService
            $resource->fastUpdate('ticket_id', $ticketCreateService->getTicket()->ticket_id);
        });
    }
}