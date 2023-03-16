<?php

namespace App\Models;

use Database\Factories\RelaySwitchTimerSequencerFactory;

/**
 * @method static RelaySwitchTimerSequencerFactory factory()
 *
 * @mixin RelaySwitchTimerSequencer
 */
class RelaySwitchTimerSequencer extends Model
{
    use IsPart;

    const MORPH_ALIAS = 'relay_switch_timer_sequencer';
    /* |--- GLOBAL VARIABLES ---| */

    public    $incrementing = false;
    protected $table        = 'relay_switches_timers_sequencers';
    public    $timestamps   = false;
    /* |--- FUNCTIONS ---| */
    /* |--- RELATIONS ---| */
    /* |--- ACCESSORS ---| */
    /* |--- MUTATORS ---| */
}
