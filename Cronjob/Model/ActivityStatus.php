<?php

/*
 *  ================================================================================
 *  @copyright(C) 2012 General Electric. ALL RIGHTS RESERVED.
 * 
 *  This file contains proprietary and GE CONFIDENTIAL Information.
 *  Use, disclosure or reproduction is prohibited.
 * 
 *  File:  ActivityStatus.php
 *  Created On: 14-Nov-2012 11:52:05
 *  @author: osvaldo.mercado <osvaldo.mercado@ge.com>
 *  @version 1.0.0
 *  @category Model
 *  @link     
 *  @package Model
 * 
 */

namespace Cronjob\Model;

/**
 * Description of ActivityStatus
 *
 * @author osvaldo.mercado
 */
class ActivityStatus {
    const RUNNING = "RUNNING";
    const IDLE = "IDLE";
    const ONHOLD = "ONHOLD";
}