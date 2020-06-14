<?php

namespace VersoBit\ResourceTickets\NF\Tickets\Repository;

class Category extends XFCP_Category
{
    public function getCategoryTitlePairs()
    {
        $categories = $this->finder('NF\Tickets:Category')
            ->order('display_order');

        return $categories->fetch()->pluckNamed('title', 'ticket_category_id');
    }
}