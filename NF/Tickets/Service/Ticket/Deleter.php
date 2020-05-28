<?php

namespace VersoBit\ResourceTickets\NF\Tickets\Service\Ticket;

class Deleter extends XFCP_Deleter
{
    public function delete($type, $reason = '')
    {
        $result = parent::delete($type, $reason = '');

        $this->disassociateAssociatedResource();

        return $result;
    }

    protected function disassociateAssociatedResource()
    {
        $ticket = $this->ticket;

        // Fetch resource and change `ticket_id` to null (0)
        // TODO: use entity relationship here
        $resource = $ticket->Resource;

        if($resource){
            $resource->fastUpdate('ticket_id', 0);
        }
    }
}