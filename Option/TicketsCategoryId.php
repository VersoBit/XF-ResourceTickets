<?php

namespace VersoBit\ResourceTickets\Option;

use XF\Option\AbstractOption;

class TicketsCategoryId extends AbstractOption
{
    public static function renderOption(\XF\Entity\Option $option, array $htmlParams)
    {
        /** @var \NF\Tickets\Repository\Category $categoryRepo */
        $categoryRepo = \XF::repository('NF\Tickets:Category');
        $categories = $categoryRepo->getCategoryTitlePairs();

        return self::getSelectRow($option, $htmlParams, $categories);
    }

    public static function verifyOption(&$value, \XF\Entity\Option $option)
    {
        if (!$value)
        {
            $value = null;

            return true;
        }

        /** @var \NF\Tickets\Entity\Category $category */
        $category = \XF::finder('NF\Tickets:Category')->whereId($value)->fetchOne();

        if (!$category)
        {
            $option->error(\XF::phrase('vb_resourcetickets_ticket_category_not_found'), $option->option_id);

            return false;
        }

        return true;
    }
}