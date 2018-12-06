<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Model;

class Status
{
    const STATUS_CREATED = 1;
    const STATUS_NOTCREATED = 0;

    /**
     * get available statuses
     * @return []
     */
    public static function getAvailableStatuses()
    {
        return [
            self::STATUS_CREATED => __('Created')
            , self::STATUS_NOTCREATED => __('Not Created'),
        ];
    }
}
