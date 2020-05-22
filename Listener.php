<?php

namespace VersoBit\ResourceTickets;

use XF\Mvc\Entity\Entity;

class Listener
{
    public static function resourceItemEntityStructure(\XF\Mvc\Entity\Manager $em, \XF\Mvc\Entity\Structure &$structure)
    {
        $structure->columns['ticket_id'] = ['type' => Entity::UINT, 'default' => 0];
        $structure->relations['Ticket'] = [
            'entity' => 'NF\Tickets:Ticket',
            'type' => Entity::TO_ONE,
            'conditions' => 'ticket_id',
            'primary' => true
        ];
    }
}