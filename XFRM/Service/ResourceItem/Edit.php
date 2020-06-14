<?php

namespace VersoBit\ResourceTickets\XFRM\Service\ResourceItem;

class Edit extends XFCP_Edit
{
    public function _save()
    {
        $resource = parent::_save();

        $this->changeTicketTitle($resource);

        return $resource;
    }

    protected function changeTicketTitle($resource)
    {
        if($resource->Ticket){
            // Update ticket's title to new resource title
            $resource->Ticket->fastUpdate('title', $resource->title);
        }
    }
}