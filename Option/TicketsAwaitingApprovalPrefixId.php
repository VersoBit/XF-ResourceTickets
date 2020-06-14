<?php

namespace VersoBit\ResourceTickets\Option;

use XF\Option\AbstractOption;

class TicketsAwaitingApprovalPrefixId extends AbstractOption
{
    public static function renderOption(\XF\Entity\Option $option, array $htmlParams)
    {
        // TODO: only fetch prefixes available in selected ticket category (would require options save if category is changed at same time)
        /** @var \NF\Tickets\Repository\TicketPrefix $prefixRepo */
        $prefixRepo = \XF::repository('NF\Tickets:TicketPrefix');
        $prefixes = $prefixRepo->findPrefixesForList()->fetch();
        $prefixes = $prefixes->pluckNamed('title', 'prefix_id');

        return self::getSelectRow($option, $htmlParams, $prefixes);
    }

    public static function verifyOption(&$value, \XF\Entity\Option $option)
    {
        if (!$value)
        {
            $value = null;

            return true;
        }

        /** @var \NF\Tickets\Entity\TicketPrefix $category */
        $prefix = \XF::finder('NF\Tickets:TicketPrefix')->whereId($value)->fetchOne();

        if (!$prefix)
        {
            $option->error(\XF::phrase('vb_resourcetickets_ticket_prefix_not_found'), $option->option_id);

            return false;
        }

        return true;
    }
}