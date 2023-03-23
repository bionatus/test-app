<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Constants\MediaCollectionNames;
use App\Constants\RelationsMorphs;
use App\Models\Brand;
use App\Models\PlainTag;
use App\Models\Post;
use App\Models\Series;
use App\Models\System;
use App\Models\Tag;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Seeder;

class ProductionPostsSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    /**
     * @var Brand[]|Collection
     */
    private $brands;
    /**
     * @var Series[]|Collection
     */
    private $series;
    /**
     * @var PlainTag[]|Collection
     */
    private $plainTagsIssue;
    /**
     * @var PlainTag[]|Collection
     */
    private $plainTagsMore;

    public function __construct()
    {
        Relation::morphMap(RelationsMorphs::MAP);

        $this->brands         = Brand::all();
        $this->series         = Series::all();
        $this->plainTagsIssue = PlainTag::where('type', PlainTag::TYPE_ISSUE)->get();
        $this->plainTagsMore  = PlainTag::where('type', PlainTag::TYPE_MORE)->get();
    }

    public function run()
    {
        foreach (Post::all() as $post) {
            $post->delete();
        }

        $postAuthor    = User::where('email', 'techteam@bluon.com')->firstOrFail();
        $commentAuthor = User::where('email', 'tech_@bluon.com')->firstOrFail();

        foreach ($this->posts() as $postData) {
            /** @var Post $post */
            $post = $postAuthor->posts()->create(['message' => $postData['message']]);

            $tags = [];

            $brand  = $this->getBrand($postData['brand']);
            $series = $brand ? $this->getSeries($postData['series'], $brand) : null;
            if ($series) {
                $seriesTag = new Tag();
                $seriesTag->taggable()->associate($series);
                $tags[] = $seriesTag;
            }

            foreach ($postData['issues'] as $issueName) {
                if ($plainTag = $this->getIssue($issueName)) {
                    $issueTag = new Tag();
                    $issueTag->taggable()->associate($plainTag);
                    $tags[] = $issueTag;
                }
            }

            foreach ($postData['more'] as $moreName) {
                if ($plainTag = $this->getPlainTagMore($moreName)) {
                    $typeMoreTag = new Tag();
                    $typeMoreTag->taggable()->associate($plainTag);
                    $tags[] = $typeMoreTag;
                }
            }

            if (count($tags)) {
                $post->tags()->saveMany($tags);
            }

            foreach ($postData['images'] as $imageURL) {
                try {
                    $post->addMediaFromUrl($imageURL)->toMediaCollection(MediaCollectionNames::IMAGES);
                } catch (Exception $e) {
                    // Silently ignored
                }
            }

            if ($postData['comment']) {
                $commentAuthor->comments()->create(['message' => $postData['comment'], 'post_id' => $post->getKey()]);
            }
        }
    }

    private function getBrand($name): ?Brand
    {
        if (!$name) {
            return null;
        }

        return $this->brands->first(function(Brand $brand) use ($name) {
            return $brand->name === $name;
        });
    }

    private function getSeries($name, Brand $brand): ?Series
    {
        if (!$name) {
            return null;
        }

        return $this->series->first(function(Series $series) use ($name, $brand) {
            return $series->name === $name && $series->brand_id === $brand->getKey();
        });
    }

    private function getIssue($name): ?PlainTag
    {
        if (!$name) {
            return null;
        }

        return $this->plainTagsIssue->first(function(PlainTag $plainTag) use ($name) {
            return $plainTag->name === $name;
        });
    }

    private function getPlainTagMore($name): ?PlainTag
    {
        if (!$name) {
            return null;
        }

        return $this->plainTagsMore->first(function(PlainTag $plainTag) use ($name) {
            return $plainTag->name === $name;
        });
    }

    public function environments(): array
    {
        return [Environments::PRODUCTION];
    }

    public function posts(): array
    {
        return [
            [
                'message' => 'Been there too many times',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],
                'images'  => ['https://dl.airtable.com/.attachments/51d4158cebbbfc1dedb981033210616f/876a81ad/IMG_98921.jpg'],
            ],
            [
                'message' => 'Hours, and hours and hours... until Bluon came around!',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],
                'images'  => ['https://dl.airtable.com/.attachments/e7756ad43dcc49fdfcbd3c29d4af6da7/55788bf0/ScreenShot2020-09-01at1_46_57PM.png'],
            ],
            [
                'message' => 'TONS?!!',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],
                'images'  => ['https://dl.airtable.com/.attachments/e62fdfd7cea6f1f9682daef51beaf766/52b1d5c4/ScreenShot2020-12-04at4_01_41PM.png'],
            ],
            [
                'message' => 'Somebodies got to keep the world moving...',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],
                'images'  => ['https://dl.airtable.com/.attachments/7794bf5e5263e75a1ecf100a652e6f7c/71c944fb/ScreenShot2020-12-08at10_49_38AM.png'],
            ],
            [
                'message' => 'Yup.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],
                'images'  => ['https://dl.airtable.com/.attachments/7e6f43b3faec8eaed04c2c5a839eee25/cb7bc92f/Unknown-14.jpeg'],
            ],
            [
                'message' => 'That we are!!',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],
                'images'  => ['https://dl.airtable.com/.attachments/479c7f237cb356d616ca7908c6d34665/23811d42/IMG_9891.jpg'],
            ],
            [
                'message' => 'My entire search history...',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],
                'images'  => ['https://dl.airtable.com/.attachments/73f720bab4582dc6e9c3835338ae2292/5ab18a82/ScreenShot2020-09-02at4_14_12PM1.png'],
            ],
            [
                'message' => 'Goin in blind!',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],
                'images'  => ['https://dl.airtable.com/.attachments/c9f2762a88bf0273a7ac5559f98f4201/daf51760/ScreenShot2020-12-07at11_50_12AM.png'],
            ],
            [
                'message' => 'Ain\'t that the truth. It\'s probably the TXV... 😂',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],
                'images'  => ['https://dl.airtable.com/.attachments/95a54c10c049c212aea15acee3ade518/b7bf4625/ScreenShot2020-12-08at10_53_51AM.png'],
            ],
            [
                'message' => 'Properly operating Crankcase heaters become even more important when using blends! Crankcase heaters (CCH) are used to boil off excess refrigerant in the oil sump to prevent liquid refrigerant (without any associated oil) from entering the compressor. During system off-cycles, refrigerant migrates to the coldest part of the system which tends to be the condenser/compressor(s) in cold ambient conditions. Due to the nature of blends, which all contain components that boil at higher temperatures than R-22, this issue can be exacerbated during the off-cycle.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Crankcase Heaters'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/9c0a8ed8f79e287e805241c41ebbee23/9c7c08fb/image.png'],
            ],
            [
                'message' => 'ALL the time',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],
                'images'  => ['https://dl.airtable.com/.attachments/a1cb6f1385d4b31fd4867279c171724e/1948d233/ScreenShot2020-12-08at12_41_09PM.png'],
            ],
            [
                'message' => 'Don\'t be a clown',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],

                'images' => ['https://dl.airtable.com/.attachments/5e7690b2d6205d5dc9ff66e9275e73cb/afc4be95/ScreenShot2020-12-08at12_53_58PM.png'],
            ],
            [
                'message' => 'And I\'ll do it again',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],
                'images'  => ['https://dl.airtable.com/.attachments/eb2eb3ec875125f04b3c29fab450c8b3/a7023bb4/ScreenShot2020-12-08at1_19_40PM.png'],
            ],
            [
                'message' => 'sus',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],
                'images'  => ['https://dl.airtable.com/.attachments/31e7df86e4cb2b6b833b04893a34cb0c/945485e4/ScreenShot2020-12-08at1_49_41PM.png'],
            ],
            [
                'message' => 'On part winding start compressors, one contactor energizes 1, 2, 3 and the second energizes 7, 8, 9. And 4, 5 and 6 are barred.  The reason for part wind start is pretty old school and was used for controlling the inrush current. Newer technology has since been created. However, if you find one of these you need to make sure the timing on the relay is correct. The delay between contact 1 and 2 should be 1 second +/- 0.5. You want closer to 1, so adjust if possible or replace if more than 1.5 seconds.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Compressors', 'Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'When recharging with recovered refrigerant, it\'s smart to use a filter drier in line to prevent introducing contaminants into a clean system.  A 1/4" flare filter drier works great.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Charging & Tuning', 'Contaminants', 'Filter Driers/Cores', 'Installation'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Tattletales are cool one-time use devices that can be very helpful when you are trying to catch a control that is intermittently opening. Simply wire it across the contact that is of concern. If you have multiple contacts that could be causing the issue, wire one Tattletale across each contact, the one that indicates is the one that had a contact open. In a pinch, you can also us a GMA/.05A Fuse, but you will need to add your own wires.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/83cc43eec5e3ba7ebce7567192f2defa/b70cb411/s-l640TattleTales.jpg'],
            ],
            [
                'message' => 'Applying power to the compressor windings while the compressor is in a deep vacuum can be catastrophic. The windings can easily short to ground or phase to phase causing permanent damage.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Compressors', 'Recovery/Evacuation'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Something cool about the Fieldpiece Bluetooth Probes is that they can be set up to perform a "Data Logging Function" using the Fieldpiece App.  You can program the probe to start Data Logging at a given time, take a reading at pre-programmed intervals and then shut down.  This can be very helpful if you are trying to catch a problem when you are not there.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Baselining'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'If refrigerant is contaminated, send it to a reclamation facility. At the facility they’ll separate the refrigerant into the individual component refrigerants or incinerate it in accordance with EPA guidelines.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Contaminants', 'Recovery/Evacuation'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Moisture in the refrigerant is one of the most common and costly contaminants to a system. It reduces the units efficiency and creates serious damage to compressors and other components. If undetected, moisture can lead to equipment downtime and costly repairs.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Contaminants'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Struggling to pull a good vacuum? Test your evac equipment regularly. If your equipment isn\'t capable of pulling deep vacuum when it isn\'t hooked to a system, you will never be able to pull a deep vacuum. If your equipment isn\'t capable of pulling down to less than 100 microns (should be able to reach 50 or less) when not hooked up to anything, then it may be time to service your vacuum pump, hoses, etc. 

First, hook your micron gauge directly to the vacuum pump itself, and make sure that it pulls down. Once you have determined that your pump is working properly, check your vacuum hose by hooking micron gauge to end of the hose, again making sure it pulls down below 100 microns. If you cannot pull down to less than 100 microns with the hose, change gaskets and try again.

Tip: Do not pull vacuum through a manifold. Pull directly from vacuum pump to system, using a Schrader Valve Core Removal Tool, with the core removed.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Recovery/Evacuation', 'Installation'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Analog and digital are the two primary types of signals in controls. Fundamentally, an analog signal is a "smooth and continuous" signal whereas a digital signal has to be discrete (typically this means it is a 1 or 0, ON or OFF, etc.). Here are the basics:

For the most part, analog signals are smooth and continuous signals that are typically 0-5V, 0-10V, or 4-20mA. For instance, a pressure transducer might have a measuring range of 0-500 psig and a 4-20 mA signal. If you were measuring the current of that device it would read 4mA when the pressure was 0 psig, 20mA when the pressure was 500 psig, and a proportional value when the pressure was any where in between (e.g. 12mA at 250 psig). 

For digital signals, typically there is just a minimum and maximum valve for ON/OFF, TRUE/FALSE, 0 or 1, etc. For example, a controller might output 0V when it wants nothing to happen and 24V when it wants to pull in a contactor to start a fan or a compressor.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'When you receive a printed circuit board or other control component that is shipped in an Anti-Static Bag or Static Shielding Bag, it is important to remember that an extremely small static discharge can destroy components on the board or the control. Always use ESD (Electrostatic Discharge) Protection such as a static discharge strap or other anti-static protections.  Typically, once the board or control is installed, its own grounding will help to protect it.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/0bd8dc5e63974d859216596d0d5687b8/5ac001bd/images.jpg'],
            ],
            [
                'message' => 'When to use Anti-Static Wrist Strap...
These straps are critical when working with many electronic boards and components, typically just 10 volts is enough to damage some of the components. If you can feel a static discharge spark, the voltage was between 4,000 and 35,000 volts. Use an Anti Static Strap and avoid unnecessary headaches.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/022e5cc103cdbc930c048924936b4b5c/7817170d/images1.jpg'],
            ],
            [
                'message' => 'When troubleshooting a system, it is common practice to jump out a safety switch that is suspected of failure. If the unit runs once the safety is pulled out of the equation, then you should then check to see if the safety is faulty, or if it did its intended job of keeping the unit from running in unsafe conditions! If the safety switch is bad, replace and verify that the unit does operate safely, per manufacturers design. If the safety did trip because the unit was running outside of design specifications, further troubleshooting is needed to find the cause of the trip. Bottom line, do not keep safeties jumped to keep unit running!',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Mixing refrigerants is illegal AND has other consequences:
1. The composition of the new refrigerant is no longer known and there is no longer a PT Chart available.
2. Mixing refrigerants contaminates any R-22 that could be reclaimed and potentially sold',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Charging & Tuning', 'Coils', 'Compressors'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'No joke!',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],
                'images'  => ['https://dl.airtable.com/.attachments/44c58a77815a4cd86beb36f7e5144205/7e627a4b/Realizingthereareactually35kmanualsinthere11.png'],
            ],
            [
                'message' => 'Saturated refrigerant is simply refrigerant that is changing between liquid and vapor. After the refrigerant becomes 100% vapor, any heat that is added to the vapor is considered to be Superheat. After the refrigerant becomes 100% liquid, any heat removed from the liquid is considered to be Subcooled.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Charging & Tuning'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Frosting at your distributor or evaporator inlet while working with refrigerant blend may be normal. 

First, make sure your refrigerant charge is correct. Then, check that your hot gas bypass is working properly. If everything checks out normal, and the frosting isn\'t extensive, don\'t sweat it! Some frosting at the distributor or evaporator inlet is normal with refrigerant blends due to their inherent glide.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Hot Gas Bypass'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'What\'s wrong in this photo?',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],
                'images'  => ['https://dl.airtable.com/.attachments/91bd80c937dbaf4761495a2df160cbc1/7fcf8dc8/IMG_1853.jpeg'],
            ],
            [
                'message' => 'says tech support when you\'re still on the line after a few hours',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],
                'images'  => ['https://dl.airtable.com/.attachments/6d8b2039ad9a4066c60934d055c4f08f/b09281ac/fb.png'],
            ],
            [
                'message' => 'When checking 3 phase motor windings, check to make sure they are all reading the same Ohms - each winding should have the same resistance. Read L1 - L2, then record reading. Read L1 - L3, then record reading. Read L2 - L3, then record reading. If all 3 readings are the same resistance, then the winding should be okay. The exception to that rule is when all the windings are ground evenly.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Compressors'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Recovery cylinders can be filled to 80% of their rated capacity. Therefore a 50lb cylinder can hold 40lbs.  
50 X 0.80 = 40lbs. 
Always look for the W.C. stamped on the cylinder and do not exceed that number.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Recovery/Evacuation'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'A 30 lb. refrigerant cylinder typically weights 17lbs. This can be verified by weighing the empty cylinder before use. The empty weight is referred to as “Tare Weight”. The Tare Weight is supposed to be stamped onto the cylinder, most often on the handle. 
Always look for the W.C. stamped on the cylinder and do not exceed that number.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Recovery/Evacuation'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'What will cause a phase monitor to trip randomly but no high or low voltage issues can be found?',
                'comment' => 'Most phase monitors will watch for phase imbalance and can be easily missed. There is a certain amount of voltage difference allowed when checking from all 3 phases. If the allowable difference is exceeded an alarm can occur. Sometimes a monitor to record power is needed to find these issues.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Mufflers on compressors are used in a system that has excessive noise in the discharge of the compressor. This is usually determined by the unit manufacture and installed when noise is over a given amount.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Compressors'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'If you can\'t figure out why a fan is not operating, check for a malfunctioning or dead smoke detector. Always check ductwork for the detector and verify who is responsible for this item as it could be the fire alarm company. Remember, do not mess around with the wiring unless the alarm company has been notified first!',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls', 'Safety Devices', 'Fans'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'If your system is throwing random codes, check for interference between high voltage and low voltage wiring. If low voltage is not shielded and if the compressor kicks on, it could create interference and throw a code. Fix the issue by running a low voltage wire away from high voltage wire and even shielding the low voltage wire.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls', 'Thermostats', 'Fault Codes'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'When you see another HVAC tech...',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => ['Memes'],
                'images'  => ['https://dl.airtable.com/.attachments/dd37660d79c064701e25556a51830ca4/133a4d7a/sandler.png'],
            ],
            [
                'message' => 'Here is one you do not see everyday.  Unit was tripping the breaker when the system shut down. Seemed like power was jumping from L1 to L2 when the blower contactor opened. I checked the contacts and found them to be very clean. I cycled the unit again and this time was watching the blower motor when the unit shut down. I saw sparks coming out the lead end of the motor.  Further checks found that the indoor motor had bad bearings and was dropping the rotor onto the stater and shorting out!',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Contactors', 'Fans', 'Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'If your low voltage fuse is blown, make sure the contactor coil is not shorted to ground. Most techs check low voltage wiring for shorts and change the fuse, then let it ride. However, when the unit calls for cooling they see the fuse pop again or the transformer blow, all because they overlooked a simple issue of a bad contactor coil. The contactor coil resistance is usually 8-20 ohms on a 24v circuit.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Contactors'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/dffd5afcf083383de5f50f3154e28177/48d16d40/ContactorCoil.jpg'],
            ],
            [
                'message' => 'The primary reason for a Hot Gas Bypass Valve is for capacity control. When the load decreases and the metering device turns down to a minimum position, the suction pressure will drop.  The Hot Gas Bypass Valve will begin to open and inject Hot Gas into the evaporator to create a false load.  In turn the suction pressure will rise.

A secondary function of the Hot Gas Bypass Valve that is often overlooked, is oil return.  When the TXV or EXV pinches down the refrigerant mass flow decreases and the velocity drops, this can leave the oil hanging out in the system and not returning to the compressor where it is needed.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Hot Gas Bypass'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Transformer breaker tripping when calling for cooling, what do you check?',
                'comment' => 'Remove the thermostat R wire at the control board. See if the breaker will reset. Then with the red wire still off the board, use your jumper and see if you can jump R to G and see if it will stay running. Then, run another jumper to Y1, if the breaker trips you need to check the outdoor unit for shorts and grounds. If everything works, then check the thermostat wires for shorts and grounds.  If you are still having trouble finding the issue, feel free to call us on our 24/7 tech support line in the app.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'If you want to add pressure safeties to a new (or old) system, and your line sets are not charged yet, install them at the indoor unit and break the red wire or the Y1 cooling wire. The wires will not be exposed to the elements of sun, dogs, or weed whackers that are common at the outdoor unit.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Buzzing or noisy contactors don\'t always mean the contactor is bad. Sometimes metal shavings or particles that are pulled in from the magnetic field prevent the two halves of the magnet from pulling together and seating properly. You can remove the back or mounting plate from the contactor and remove any debris, or remove the rust from the mating surfaces.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Contactors'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Adjustable TXVs - What to know:
In a nutshell, adjustable TXVs can be adjusted to achieve the required Superheat on an HVAC system. Keep in mind, that not all TXVs are adjustable though. If the TXV is adjustable, there will be a hex cap at the base. Remove this to access the adjusting screw. Turn the screw clockwise to increase Superheat. Turn the screw counter-clockwise to decrease Superheat. Obviously, you\'ll want to closely monitor the system while you make adjustments and give the system time to settle down in between adjustments. Make your life easier by investing in some quality Bluetooth instruments so you don\'t have to constantly move back and forth between the evaporator and condenser.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'When arriving at a residential job, do these three things first!
1) Find the Thermostat and make sure it\'s on Cooling
2) Turn fan to ON
3) Set Temp to 5 degrees below room temp
This ensures that the machine should be running and helps you rule out if it\'s just a thermostat issue.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Thermostats'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Passive vs Active Metering Devices:
Passive Metering Devices are typically Cap Tubes, Pistons, Orifices, Accutrols or Flowraters. Active Metering Devices are typically Thermostatic or Electronic Expansion Valves.

The Passive Devices vary by the size and length of the opening (or orifice). Flow varies largely by ambient conditions and load on the evaporator.  The actual superheat can vary greatly, between 5 °F and 50 °F degrees.

Active Metering Devices respond to the load on the evaporator coil achieving far better control and efficiency.  This is achieved by measuring the superheat at the exit of the evaporator  and opening or closing the valve to maintain the desired set point.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Here are some general rules for refrigerant piping. Generally, your suction line should slope slightly back towards the compressors to help with oil return. As a general rule, the slope should be 1/2" for every 10\' of horizontal run. If the evaporator is lower than the compressor and has more than 10\' of vertical runs, then it may be necessary to pipe in traps, inverted traps or double risers but always refer to OEM recommendations for the specific guidelines.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Oil'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Can I replace a capacitor with a larger one since I don\'t have that size on my truck?',
                'comment' => 'Yes if you\'re in a pinch and it\'s temporary, or within 10% OEM spec. Capacitors size is critical to motor efficiency. A motor that requires a 7.5 mfd capacitor will not work with a 4.0 mfd capacitor and a motor will not run properly with a weak capacitor. In both instances, be it too large or too small, the life of the motor will be shortened due to overheated motor windings! Always use the right capacitor for the job.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Capacitors'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/ce47bdedaf14501065c93efd65b484ea/5cb2bfb7/Capacitor-group_3250.jpg'],
            ],
            [
                'message' => 'How to check a capacitor without shutting off a unit? There is a very simple test - measure voltage and start amperage at capacitor and plug the numbers into the following: Start Winding Amps X 2,652 ÷ Capacitor Voltage = Microfarads. 
Capacitors also have +/- 6% ratings so keep that in mind when testing them.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Capacitors'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Test a run capacitor while unit is running...most techs think you can only check a run capacitor with unit off and power shut down, but that is not the case - you can get a more accurate mfd reading while unit is running by following this simple process: 
1. Measure Volts across Herm and Common
2. Measure Amps across Herm and Start
3. Multiply Amps by 2652
4. Divide results from step 3 by Volts from step 1 
5. Result is actual MFD!',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Capacitors'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Testing Run Vs. Start Capacitors:
If the unit is not running you need to at least pull one wire off the capacitor to make a good mf reading. If your wires are connected then you will read the motor windings as well.  
On start capacitors that are soldered in the motor, they have a centrifugal switch that removes them from the circuit once up to speed, either de-solder the wire or if you can access the switch and open it to read those.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Capacitors'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/7c3314618ffe7dffdbde4580c541a374/7f77c82c/testingcapacitorwithpower.jpg'],
            ],
            [
                'message' => 'Dual capacitors, How to read when you have a 35/5 uf ?
You will see the terminals marked as C = Com, Herm = Compressor, and Fan = OFM.  to check them with no power remove all the wires, careful to mark the wires so you can put them back.  With your meter on UF, check C to Fan, this should be 5.0 +- 10%.  and C to Herm should be 35 uf +- 10%.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Capacitors'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Sub-cooling or Superheat first? When charging a system that is using a TXV, pay attention to both Sub-cooling and Superheat simultaneously.  If a TXV is fully closed high Sub-cooling and low Superheat can be seen.  If low Sub-cooling and high Superheat is observed then there is not enough refrigerant available to properly feed the TXV.  Keeping the Sub-cooling reading positive while adjusting the TXV will provide an adequate amount of liquid to properly feed the valve.  Typically the correct Superheat will be 12 degrees at the suction line leaving the Evaporator Coil and 18 to 20 degrees at the compressor suction.  The Sub-cooling will typically be 8 to 10 degrees.  Going a little slower will help to avoid over charging.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Charging & Tuning'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'What pressures are a TXV set at, and how can you adjust one?',
                'comment' => 'The typical TXV comes factory set for 8-12 degrees of evaporator superheat.
The TXV cannot be adjusted open or closed, it is a modulating valve. Turning the adjustment stem clockwise will only increase spring pressure causing a higher superheat. Turning the adjustment stem counterclockwise will decrease spring pressure reducing superheat. ',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Charging & Tuning'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Why do you have to screw in (tighten) TXV stem when converted to Bluon TdX 20 (R-458A)?',
                'comment' => 'Bluon TdX 20 (R-458A) runs at lower pressures than R22. Since the evaporator pressure will be less when converting to TdX 20, and the counteracting bulb pressure will remain the same, the spring tension must be increased to prevent the valve from overfeeding resulting in low superheat and high saturation temperature.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Charging & Tuning'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'If a TXV power head has lost its charge, the TXV will not open. Inspect the capillary tube for any cracks or punctures. If the system is running with high superheat and it is suspected that the bulb has lost its charge, immersing the bulb in warm or hot water should cause the TXV to open. Be aware - this should be done with caution as it could cause compressor flood back. If the power head is removable, the system could be recovered and power head removed. Observing the diaphragm, hold the bulb in your hand and see if the diaphragm reacts. If the diaphragm moves, the power head may be good. If it doesn\'t move, then the power head has lost its charge and should be replaced.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Thermostatic Expansion Valve sensing bulb tips:
- The sensing bulb should be installed on the suction line at the outlet of the evaporator on a horizontal line.  It should not be installed on or after a trap.
- The suction line should be clean to allow for good heat transfer.
- On a 7/8" or larger suction line the bulb should be installed at 4 or 8 o\'clock.  For smaller lines the bulb can be installed on the top of the suction line.
- Do not install the bulb on the bottom of the suction line, it can be affected if there is oil in the suction line.
- Using a thermal transfer paste will help with heat transfer.
- Use Stainless Steel hose clamps or Copper Strap to mount the sensing bulb to the suction line (2 straps work best).
- Insulate the suction line and sensing bulb, this prevents ambient air from influencing the sensing bulb.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Charging & Tuning'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'When using an R-22 replacement, you will likely need to adjust the TXV. When adjusting the TXV, it is important to first charge the unit to obtain the desired subcool. Once target subcool is achieved, adjust the TXV valve to obtain the desired Superheat. It may be necessary to alternate between increasing charge and making TXV adjustments to achieve target subcool AND superheat.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Charging & Tuning'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'What should your maximum velocity be in a ductwork system?',
                'comment' => 'Try to keep your velocity below these numbers to reduce noise and increase comfort:
- Supply 1000 feet per minute (FPM)
- Return 800 FPM
- Branches 600 FPM
- Grille 400 FPM
- Filter Grille 300 FPM',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Airflow'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'There is some confusion when it comes to Static Pressure and Air Flow. You can have a very high Static Pressure and absolutely no Air Flow. Static Pressure is the pressure exerted against the interior wall of the duct, it is read with a manometer and is the difference between the pressure on the inside referenced to the outside of the duct.  CFM (Cubic feet per minute) is measured typically by reading the Air Velocity (Feet per minute) and multiplying it by the area of the duct (remember to convert the area of the duct to square feet for the calculation, don\'t forget about the duct lining).  If a duct had an opening in it, and you compared the the air flow through that opening - a higher Static Pressure would flow more air than a lower Static Pressure.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Airflow'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Down and dirty test on a 0-10VDC or 4-20mA actuator: Simply connect a 9V battery to the 0-10VDC terminals to drive the actuator to approximately 90% (9VDC). Or add a 510 ohm resistor in series for the 4-20 mA actuator and it will drive to approximately 85% (17.6 mA).',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/fc2584c4c0b11384b3e0d7cd9eb05e24/f91af858/bhold9v-1.jpg'],
            ],
            [
                'message' => 'It is always a good idea to inspect the location of the sensing bulb for the TXV.   The bulb should be mounted securely onto the suction line.  It is important that the bulb is making good contact and there is no corrosion between the bulb and the suction line.  In addition the bulb needs to be insulated so the bulb is not being influenced by surrounding ambient conditions.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Adjustable sheaves are used to change the volume of air a blower can move. During startup the sheave can be opened and closed to change the diameter of sheave causing blower RPMs to change, resulting in more or less airflow. Adjustments can be made to match correct motor amps, CFMs and static pressure. It is recommended that after final adjustments are made to replace adjustable sheave with fixed for proper belt and motor life.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Belts & Pulleys', 'Airflow'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'When replacing belts on a blower motor with multiple belts, it\'s best practice to order belts that have been manufactured together at the same time to prevent having slightly different lengths. These are called Match Sets. Not following this best practice can can cause uneven wear to the drive assembly.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Belts & Pulleys'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'It\'s good practice to carry a box of adjustable link belts for when you need a new belt, but the correct size is not available. These belts come in segments that can be made to any length and will run as a temporary or long term blower belt replacement.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Belts & Pulleys'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Belts and pulleys can wear out prematurely if the belt is not properly tensioned. Make sure to tension your belt properly during your PMs!',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Belts & Pulleys'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'To know if your ECM motor is bad w/o a testing tool (Supco ECMPRO Universal ECM Tester or TECMate Pro ECM/Eon Motor Tester), you can test using your multimeter, with a call for fan: 
1. Verify you have control voltage (typically 24VAC) going to the motor
2. Verify you have proper Input voltage to the motor (see motor nameplate for spec\'d voltage)
3. If proper control voltage and proper Input voltage to the motor and the fan is not running, you could have bad motor OR bad module. In most cases, you can replace each individually, but most people recommend changing both.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Fans', 'Airflow'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/15ce131b98dcabd67be9f6f2a65b1b56/96b3b02a/ScreenShot2020-12-23at9_34_31PM.png'],
            ],
            [
                'message' => 'Do not change out an ECM to a PSC. While the cost of PSC motor and parts needed for conversion (capacitor, relay, extra wire, etc.) may be much less than the OEM replacement ECM motor, it may not be the smart choice. The ECM motor is more energy efficient, and helps ensure designed CFM when the load varies (dirty filter, dirty squirrel cage, wet coil, etc.). There are also aftermarket ECM motors that work as well as the OEM motor and do not cost as much.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Fans', 'Airflow'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Having issues with a three phase ECM blower motor and want to check if the motor is good? First check for continuity between the three power connections after disconnecting power to them. If they are good, then check ohms for similar readings. If one is way out of whack or shows OPEN, then it is most likely a bad winding. Also, check between each leg and the case of the motor for shorts.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Fans'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'If you have extreme air noise in the return and the blower door is extremely hard to get off or is caving inward, you most likely have a return blockage. In commercial buildings when ducts penetrate through walls they are required to have fire dampers. The fire dampers have zincs designed to melt in heat and close them. They do however break prematurely and need to be repaired.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Airflow'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Setting up superheat on multiple TXV\'s simultaneously:

When setting up the Superheat on multiple TXV\'s, waiting around for the system to settle after an adjustment is time consuming.  Analog gauges are not that accurate.  Using a Fieldpiece Pressure Probe (JL3PR) and up to 4 Temperature Probes (JL3PC) with the Field Piece App you can adjust the TXV\'s more easily.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Charging & Tuning'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Need to clean the evap coil, but can\'t get get access to it due to duct installation? Pick up a bunch of pieces of insulated 12x12 or 16x16 square sheet metal next time you are at the duct shop and keep them on the truck - now you can cut a hole in the ductwork large enough to clean the coil and use the squares to cover the hole - screw it in place and then seal it with mastic tape - may be a hack but much better than not cleaning the coil if it is filthy. This can be especially useful if there is already a hole in the duct work and someone just tried to tape over it - tape won\'t usually hold during cooling system and the unit will start sucking in unfiltered air. Don\'t be this guy.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Airflow'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Do you use flex duct properly? Here are a few pointers to ensure the best outcome:
1. Use only what you need. Plan for short, straight runs.
2. If turns are unavoidable, make sure you don\'t create any kinks in the duct. 
3. Make sure supports are no more than four feet apart to minimize sagging.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Airflow'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Typical airflow for standard comfort cooling equipment is between 350-450 CFM/ton. For most applications, 400 CFM/ton is appropriate. For humid climates, 350 CFM/ton is more appropriate and for dry climates 450 CFM/ton may be more appropriate. Dehumidification or high velocity systems can be as low as 200 CFM/ton.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Airflow'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'The "20 degree F Delta-T" can be misleading under varying conditions. Most systems will produce 20 F delta-T under nominal "AHRI" conditions (80 F db / 67 F wb Return Air Temperatures, 95 F db Outdoor Temperatures, and 400 CFM/ton). As you vary from these conditions, your Delta-T will change. For example, if instead of running 400 CFM/ton the system has clogged return air filters and is actually only running 300 CFM/ton, then your delta-T can be significantly different than the nominal delta-T. Generally, low airflow results in a higher delta-T (and vice versa). As another example, let us say that the return air temperatures are actually 67 F db / 55 F wb and 75 F db Outdoor temperature. This will also affect the actual Delta-T. Generally, lower load / lower ambient conditions will result in a lower Delta-T.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Airflow'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'When you see an alarm for low DC voltage, check the DC buss voltage. Check the fuses in the VFD to make sure they are not open. Also, check the capacitor banks and look for any signs of damage to the capacitors.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['VFDs'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Heat Sequencers are basically a "Timed Delay On" and "Timed Delay Off" switch.  They have between two to seven individual switches in one or more stacks. A 24Vac voltage can be applied simultaneously to a heater for each of the stacks. The heater causes the switches to close in a sequential order.  In many cases, the first stage is the Fan and the first stage of electrical heat together.  The switches turn off in the opposite order that they came on - first on is last off.  The biggest reason for the "Heat Sequencers" is to prevent an excessive current inrush if two or more heating elements came on at the same time.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls', 'Heating'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'To replace an internal drain pan in an air handler, you may not need to cut into the refrigerant pipes to pull out the coil IF the coil is connected to a lineset. As a precaution, pump the system down and then slowly pull the coil section out of the air handler. Replace the pan and slide the coil back in! Make sure to reattach the pan clips (if applicable) to hold it in place.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Coils'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Leaving Water Temperature sensor failing on a Chiller?
Typically chiller controls are looking at Leaving Water Temperature, or Leaving Fluid Temperature, and controlling by that input.  Often, the return temperature sensor is essentially a "Courtesy Readout".  Therefore, in a bind or until we can get a new sensor in, we can swap the two sensors.  The return reading is really not essential for basic operation.  
Exception:  under certain applications, the chiller can be programmed to look at both return and supply.  This usually occurs in process cooling or EMS type applications.  Look in programming to make sure.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Low limit or Low Leaving Water Temperature Fault. When this fault occurs it is important to look at the whole chiller.  Check the sensor first, often the sensor is giving a bad reading.  Check the chilled water flow and be sure it is at design.  In the last few years there has been a move toward variable flow systems which can vary the flow through the chiller.  In these systems, if a valve in the chilled water loop fails or is responding too slowly, it can cause the chilled water flow to drop below design which can lead to low leaving water temperature.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Fault Codes', 'Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Some commercial units have smoke detectors in the ductwork to shut down the unit, stop all airflow, and the stop spread of smoke and fire in duct. This helps contain the fire to one area of the building and contains the damage to the original location.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'When your unit has 2 or more condensing fans but only 1 is operating, make sure the head pressure control is set correctly and that the operating conditions are actually calling for the controlled fans to be on - they will not be operating until the pressures are above setpoint.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls', 'Fans'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'A filter drier is stopped up or likely needs to be replaced, when you have a temperature and a pressure drop across the drier more than 3 psi for A/C or 1 psi for low-temp refrigeration.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Filter Driers/Cores'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Filters driers are used to help clean the refrigerant circuit of harmful contaminants and debris. They can be in both suction and liquid lines, but it is not recommended to install a filter drier in the discharge/hot gas line. Filter driers are recommended after a refrigerant circuit is opened for service and after a compressor replacement.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Filter Driers/Cores'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Remember to always test sensors before condemning boards! Bad sensors are often overlooked as the cause of system issues. Don\'t spend the time, energy and effort to replace the board, only to have the same code pop up as soon as the system is powered up.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls', 'Fault Codes'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Call backs for unit not cooling, on arrival all is working normal.  

Most condenser fan motors have within the winding a thermal overload, which will cause the motor to shut off when the internal temperature gets too high.  A common scenario is the call comes in as no cooling, the homeowner has shut the unit off.  On arrival you start the unit, all is operating normally.  This can be because the thermal overload has reset and it may take considerable time for the motor to heat up again.  
Be sure to check the amps on the motor, actual compared with nameplate.  Check the capacitor.  Check the fan shaft, there should be little to no "play" in the shaft.  Any movement or "play" is indicative of worn bearings.  Any signs of oil near the shaft on the motor is indicative of a failed seal on the bearing.  All of these can contribute to higher than normal amp draws.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'What are the different filter drier/core types and what are the uses?',
                'comment' => 'There are driers and replaceable cores for both suction and liquid lines. Driers and cores need to be sized properly by refrigerant and the circuit capacity. Liquid line driers are used to filter and clean the liquid refrigerant of moister and small particles. Cool-only systems typically use one-way driers that must be installed with the arrow pointing in the direction of flow. Heat-pump systems require bi-flow driers which can also be used in Cool-only systems. Suction driers are typically used to help clean a system after a compressor burnout by removing acid and sludge. Most driers are offered with a high acid removal option. ',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Filter Driers/Cores'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'ALL blends should be charged as a liquid. Yellow Jacket and JB Industries both offer a Liquid Charging Adapter. These adaptors have a small orifice that causes the refrigerant to vaporize just before going into the system. It goes inline with the charging hose and takes away any guess work.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Charging & Tuning'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/d138d8880466d2e8c7a4d6dbce6222ba/0cde3d2d/bluecap.jpg'],
            ],
            [
                'message' => 'Maximum pressure drop across a suction drier can vary based on:
- Type of drier
- Type of refrigerant
- Application 
- Evaporator temperature
- Permanent or temporary install
- Filter manufacture    
*Always refer to filter drier manufactures documentation for maximum Pressure drop',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Filter Driers/Cores'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Green slime forms on refrigerant circuits when there\'s a low side leak. This creates high temperatures which combined with water and air pulled in the system, degrades the white mineral oil by oxidation and hydrolysis. These degradation products attack the copper components and create the green oil/slime. Green slime is more prevalent in systems using a compressor that has a bronze or other largely copper containing bearing. Install a low pressure control and fix the low side leak to prevent green slime oil. Use proper refrigeration clean up procedures like the one in this link from Copeland:
http://www.hvacrinfo.com/cope_ae_bulletins/cope_grnslm.pdf',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Contaminants', 'Leaks'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Contactor is pulled in, but system is still not running and there are 24V to contactor. What\'s going on?',
                'comment' => 'Pull the contactor and check the contacts. Small bugs love to climb in and sometimes get squished when contactor pulls in. The bugs then block electric flow through contacts. This can lead to arcing of the contacts and cause the contactor to burn up!',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Contactors'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/abe55b08eef81809bded03c29bcd0145/4a0cefb3/Bugsincontactor.jpg'],
            ],
            [
                'message' => 'When recovering refrigerant into a recovery cylinder, does it make a difference whether it goes into the liquid valve or gas valve?',
                'comment' => 'Not a solid answer as to which one is preferred. But if you are doing a recovery, you should always be recovering liquid. So then if you recover into the liquid side then that should work best by keeping pressures low.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Recovery/Evacuation'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Charging systems that use Passive Metering Devices (cap tubes, pistons etc...) requires a Target Superheat.  However with Passive Metering Devices, Target Superheat is constantly changing based off Outdoor Air Drybulb Temp and Return Air Wetbulb Temp. Using measureQuick and Fieldpiece Probes, gives a continually adjusting and extremely accurate Target Superheat.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Charging & Tuning'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'If system has 2 thermostats and only 1 unit - look for a zoning board usually located somewhere near the IDU. Most have diagnostic lights indicating operation of each zone and should have 24V power usually requiring its own transformer. Many issues with these zone boards can be traced to pulling 24V from main unit transformer.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Zoning', 'Controls'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/0429bd362c26c50c86f5f8187f3942be/26a76429/Zoneboard.jpg'],
            ],
            [
                'message' => 'When charging a fixed orifice unit, target Superheat is almost all that matters.  Determine the target SH (picture attached). Start your charge at 70% of the nameplate or what you removed. Go slow, giving the unit time stabilize as you add refrigerant. To tune the unit to optimal performance, slowly meter the refrigerant into the suction line to obtain your desired Superheat.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Charging & Tuning'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/0b0103100abd89a6a4edd5eab98202ad/66f0d4fb/res_manual_appendix_l_TableL-1.jpg'],
            ],
            [
                'message' => 'Contactor pulled in, but compressor not running?',
                'comment' => 'When checking a system and you find the low voltage correct but nothing is running, check for main power at the disconnect. Check and reset all breakers to the system, but first turn off the low voltage to prevent the breaker from tripping when you reset it. The inrush amps can trip the breaker if nothing else is wrong with the system. You should also check the system, compressor and fan motor for shorts and grounds before turning the main power back on. Disconnect the wires from the contactor before testing, if possible check at the compressor terminals.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Compressors', 'Heat Pumps'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'There are typically 2 pressure testing ports on suction line driers - one on the inlet side and one on the outlet side. These ports check pressure drop across drier to determine if drier pressure drop exceeds the manufacturer\'s maximum allowable pressure drop and needs to be removed or replaced.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Filter Driers/Cores'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Bad expansion valve, but going out on high head pressure? TXVs may go out on high head pressure in systems with microchannel condenser coils.  
Typically, with a bad TXV you would go out on low suction. However, with a microchannel condenser coil there is less volume so you pump down and stack liquid in the condenser, which backs the pressure up, causing you to go out on high head before you go out on low suction. 

Because of this, do not pump it down when you are doing repairs!',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Why is my amp draw high after replacing condenser fan motor?',
                'comment' => 'Check you condenser fan motor RPMs on the old motor and match to new motor. It is common to have 825 RPM motors on units. Fan motors can be 825 or 1075 RPM\'s. If a 1075 RPM motor is installed on blade that is designed for 825 rpm there will be a significant increase in amps due to the extra air volume being moved. Best practice is to match motor RPM\'s as close as possible to original.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Fans'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'I have 2 propeller fans and one is running backwards. What\'s going on?',
                'comment' => 'On 115 Volt motors, 2 wires are for power and 2 wires are for capacitors, with identical motors and blades.  If you find this issue, double check your capacitors as one of them is in need of replacement. Even on CFM\'s that can happen, as one blade turns correctly the other will start and run reverse.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Fans'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Can I replace an 825 rpm condenser fan motor with a 1075 rpm?',
                'comment' => 'No, the motor rpm and blade are designed together for the motor and air flow needed to exchange heat properly through the condenser.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Fans'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Which line should you open first on a brand new unit? Liquid or Suction?',
                'comment' => 'After you\'ve pulled your vacuum, you should open the liquid line. After that, break the suction and slowly open that one.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Installation'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Sometimes the insulation on the pressure switch can melt, causing shorting and blowing fuses, etc. - always ohm out the safety wiring to verify it is not shorted.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'When charging a circuit with a compressor unloader, it is necessary for the compressor to be loaded fully to charge. It is best to fully load all circuits to 100% for charging to proper superheat and subcool.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Charging & Tuning'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'You can use a low ambient kit to maintain sufficient head pressures for indoor cooling despite low outside temperature. It works by slowing the condenser fan to increase the head pressure.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Low Ambient'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'If you find abnormally high head pressure on a heat pump, always look at things that could reduce indoor airflow (dirty filters, coils, blowers, duct issues).  Low airflow can cause really high head pressure because the indoor coil becomes the condenser in heat mode.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heat Pumps', 'Airflow', 'Fans'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'You should not charge a heat pump unit in heat mode. You can add refrigerant to it if absolutely needed, but you should not charge and tune in heating mode. To properly charge a machine it has to be in cooling.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Charging & Tuning'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Why is my heat pump freezing up?',
                'comment' => 'Heat pumps work by reversing the normal cooling cycle so that the outdoor unit becomes your evaporator (absorbing heat from the outdoor air) and rejecting that heat at the indoor coil (into the return air). To absorb heat from the outside air, the outdoor unit will typically be 20-25 °F colder saturation temperature than the ambient air temperature. If it\'s 50 °F outside that means your outdoor unit saturation temperature is going to be between 25-30 °F and will likely start to ice up. This is perfectly normal for heat pumps operating in colder ambient conditions as long as the defrost cycle is working. If it is much colder than 50 °F outside then significant amounts of ice can build up and start affecting the heat pump\'s ability to satisfy the load, at which point supplementary heating will be needed (i.e. gas or electric heat).',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heat Pumps'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Should I install a bi-flow liquid line filter drier on all heat pumps?',
                'comment' => 'Yes, in a heat pump, the liquid line is always the liquid line but the flow direction goes from outside / in during cooling mode and inside / out during heat mode. For this reason, we must use a bi-flow filter drier on the liquid line so it can filter the refrigerant in both directions.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heat Pumps', 'Filter Driers/Cores', 'Installation'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/30be66f61ff3d197e250bbd796b1ca04/6a16894f/biflowfilterdrier.jpg'],
            ],
            [
                'message' => 'The reversing valve solenoid is an electromagnetic coil that mounts onto the reversing valve and is generally 24V on residential heat pumps. The solenoid does not actually shift the main valve, it only shifts a much smaller pilot valve that then uses system pressure to shift the valve. The solenoid should never be energized, unless it is properly mounted on the valve or it can overheat and fail.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heat Pumps', 'Controls'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/9cfe7548a96ba6abf26a4fec64654831/04862a65/reversingvalvesolenoid.jpg'],
            ],
            [
                'message' => 'Wiring a thermostat for heat pump O vs B...Most units show the "O" terminal hooked up for heat pump (although Rheem/Ruud/Weatherking and a few others use the "B" terminal instead) - these terminals are used to actually energize the reversing valve - the "O" terminal activates the valve in cooling, the "B" terminal activates in heating. Always check the wiring diagram in the install manual and at the ODU to verify! Also make sure the thermostat is wired and programmed (if applicable) for correct operation.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heat Pumps'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Heat pump compressor not coming on in heat... 
Customers think by turning the thermostat to emergency heat that they are getting more heat. Double check the thermostat settings to make sure it is programmed for a heat pump and that the emergency heat function is not turned on. Emergency heat will lock out the compressor and only run the strip heaters. Plus, if you don\'t have strip heaters, then they have zero heat when putting into emergency heat mode.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls', 'Thermostats'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Forcing defrost on most heat pumps:
On most, but not all heat pumps you can force defrost by placing a jumper wire between the test pins on the board itself. A large blade flat screwdriver will also work to force defrost. If there are no test pins, find the correct service manual for that particular unit. When replacing the defrost board, always replace the sensor as well.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heat Pumps'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'High electric bills running a heat pump:

High energy bills are usually caused when back up electric heat is running constantly. Check to make sure the outside condenser is running and if so, make sure that the defrost is working and the condenser is not completely iced over.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heat Pumps'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'The two most common types of defrost controls are time-temperature and on-demand defrost.  Time-temperature defrost controls activate defrost at regular time intervals for set time periods, whether there is ice on the outdoor coil or not.
A demand-defrost control senses coil temperature or airflow through the coil, and only activates defrost if it detects the presence of ice. Obviously, choosing a heat pump with demand-defrost will pay a significant efficiency dividend.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heat Pumps'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Timed defrost controls defrost based on a timer that activates defrost after a given run time regardless if there is ice present on coil or not. This process is the least efficient of the two types (timed defrost and demand defrost).',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heat Pumps'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Demand defrost uses timers and sensors to control defrost only when there is ice on coil or the airflow is limited by ice. If ice is present and the timer calls for defrost, the unit will run defrost until a given time has elapsed or the ice is thawed and the sensor opens. This is the most efficient of the two types of defrost (timed defrost and demand defrost).',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heat Pumps'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Heat Pump just keeps running and will not shut off or go into defrost.
When troubleshooting a heat pump that never seems to go into defrost check a couple things right away.  Look at the control board and see what the jumper is set to for checking defrost time.  It is possible the guy before you has put the board in test and never placed the jumper back to a normal cycle.  If the coil is frosting up more, then adjust the time to less minutes. From 90 down to 30 minutes.  Also ohm out the sensor on the coil. Some are switches and are open/closed to start defrost, others are sensors and you can read the ohms and check the OEM documents to see if they are in range.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heat Pumps'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Sensors on defrost boards should never be jumped out like you would for a defrost switch - this will burn out the board and require changing the board and sensors. Always test sensors based on temperature and resistance!',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Many of the heat pumps have a high pressure switch which can be reset by finding the red button on the back side of the unit. Always check to make sure these switches have not been bypassed or cut out by prior technicians. If the switch is tripped, it can usually be caused by a dirty condensing coil or bad capacitor on condensing fan motor.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls', 'Heat Pumps'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'When troubleshooting blown low voltage fuses during heating, many techs check the low voltage wiring for shorts and change the fuse. On startup everything works fine until the unit goes into defrost. When the reversing valve energizes it blows another fuse or burns up the transformer due to a bad reversing valve coil or short to ground. When checking for low voltage issues always check coil resistance (usually 8-20 ohms on a 24V circuit) and make sure it is not shorted to ground.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heat Pumps'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/29785a451db357babb43fb06544142e7/47d6b2d2/Revvalvecoil.jpg'],
            ],
            [
                'message' => 'What\'s the difference between "Emergency Heat" and "Auxiliary Heat"?',
                'comment' => 'Auxiliary heat is used as supplemental heat in a heat pump and increases the supply air temp when the heat pump doesn\'t have the capacity to maintain space temperature. Emergency heat is used when there is an issue with the heat pump system. It can be ran independent of the heat pump and provide heat during times when the heat pump can not be run for other reasons.  Both use the same heat elements but are controlled differently. Emergency heat is active when the thermostat is set to emergency heat which also disables the compressor.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heat Pumps'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'What are some secondary heating options used with heat pump systems?',
                'comment' => 'Typically electric heat elements are used as auxiliary heat in heat pumps, but there are also other options. A gas or oil furnace can be used with heat pumps and can be set to shut down the heat pump and take over the heating when there is a call for more heat. This is called dual fuel. There is usually an outdoor temperature sensor that will shut off the heat pump below a given temp and run only gas or oil until the unit satisfies. Codes can vary by state and region as to what forms of heat are approved.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heat Pumps'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Unit blowing hot when set for cool or vice versa:
Check the O/B. If calling for the reversing valve when not needed, you will likely trace the issue to improper set up at  the thermostat. ALWAYS check the thermostat manual for installer menu and verify the set up.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heat Pumps', 'Thermostats'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/57eac1ea88260e34d3bb629583915091/6dbf978d/honeywell-thermostat-t8411r-wiring-diagram-7.jpg'],
            ],
            [
                'message' => 'How does a defrost cycle work on a heat pump?
When the outdoor temperatures gets low enough, the outdoor coil may become ice covered and require a defrost. The most common sequence is the system will switch into cooling mode, turn off the condensing fan and turn on the aux. heat until the defrost is complete. It will then switch back to normal operation once the coil is free of ice.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heat Pumps', 'Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'To determine if you have a bad P66 fan controller or fan motor, troubleshoot the controller:
1. Disconnect 6-pin connector from right side of control 
2. Place a jumper wire between third pin from the top and the bottom pin on the control, not the cable
3. If fan goes to full speed, check for input pressure
4. If there is adequate pressure, the transducer is bad and the control must be replaced',
                'comment' => null,
                'brand'   => 'Liebert',
                'series'  => 'DCS',
                'issues'  => ['Fans'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/b50d8740b403cc7f73aaad239f620222/c55de09f/P66.jpg'],
            ],
            [
                'message' => 'When working on most of the modern heaters that utilize "Flame Rectification" as a flame proof it is important to remember that the Flame Rod has 80 to 120 VAC applied to it and rectification takes place through the flame. This allows a 1 to 10 microamp current to flow through the flame to ground. It is that microamp signal that is sensed by the control board.  If you have a good flame rod with the proper voltage applied to it and no ground, you cannot complete the circuit.  Grounding is extremely important in these applications. Typically there will be a ground wire to the burner assembly and in some cases a poor ground can be a result of not reinstalling the screws that hold the burner assembly in place.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls', 'Heating'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'When doing a furnace PM, one thing that is usually overlooked is the pressure switch. Spend an extra couple of minutes pulling off the tubing and cleaning the ports that the tubing connects to. Could save you a nuisance service call!',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'On a typical Split Gas Electric system, where does the 24vac that energizes the compressor contactor come from?',
                'comment' => 'The actual 24vac power is supplied from a transformer in the furnace. The Yellow Wire from the thermostat provides the hot side of the 24vac. The common side of the 24vac can come either from the Furnace Control Board or the Thermostat.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Electrical & Wiring'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Furnace will not fire up and there\'s code for a Pressure Switch fault. What\'s going on?',
                'comment' => 'Pull the flue and shine a flashlight down both ends to check for blockage. There can be wasps nests, dead animals, etc. Always good to check.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Fault Codes', 'Heating'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Wonder about the sequence of operation for heating with an inducer motor? On a call for heat, the inducer motor starts, a pressure switch or centrifugal switch proves the inducer motor, then it energizes the ignition device (most likely a hot surface igniter) and opens the gas valve. The gas valve will open for between 2 and 10 seconds to allow time to ignite the gas and prove the flame. If it doesn\'t prove the flame, then the system shuts down on safety. This is important to know because you can trouble shoot using the progressive logic to figure out what the problem is.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heating', 'Controls', 'Igniters'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'If a heat exchanger is full of soot on newer furnace, this is probably because the unit was not fully converted to the proper fuel type and/or the pressures are out of whack -- resulting in a super rich air/fuel mix. Always verify fuel type and make sure you have the proper kit for that furnace!',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heating'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Down and Dirty Inducer Motor Test: In many cases you can simply read the current draw on an Inducer Motor, if it\'s pulling 85% of RLA it\'s probably moving the proper amount of air.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heating'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Induced Draft Motors exist to purge out the fire box prior to ignition. Once the purge is complete and the proof of draft (can be a pressure switch, Hall Effect CT or centrifugal switch on the draft motor) the ignition process can continue.  The Forced or Induced Draft Blowers pull air and gas into the fire chamber helping to improve performance.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heating', 'Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'What if the the inducer motor is running but not making the switch? Take out the inducer motor assembly and look at the wheel. On these older style 80% furnaces the wheel gets old and rusty and falling apart.  If this happens you will see the temps go up and trip the high limit as well as not make the switch.  Look for flue issues like restrictions from small animals as well.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heating', 'Fans'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Basic sequence of operation for a modern gas furnace.
1. The thermostat calls for heating. All of the safety switches are verified to be closed.
2. The draft motor starts and purges the combustion chamber.
3. The negative pressure is proven by a pressure switch, a Hall effect CT proves the draft motor is under load or in some cases a centrifugal switch proves that the motor is at speed.
4. The igniter begins the ignition process (hot surface or sparking type) and the gas valve opens.
5. The gas ignites.
6. The flame proves, typically "Flame Rectification", this allows the gas valve to remain open.
7. The combustion chamber heats up and the blower starts.  This is done either by the combination fan / limit switch or a timer.
8. If the air leaving the heat exchanger exceeds a predetermined level (High Limit or Auxiliary Limit), the gas will close to prevent damage or overheating of the heat exchanger.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heating', 'Controls', 'Safety Devices'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Inducer Motor Issues:
Inducer Motors are designed to create a negative pressure in the combustion chamber.  A few things to check if you are having problems creating the proper draft.
- Blocked exhaust vent.
- Vent caps missing or damaged.
- Inadequate combustion air, this is more prevalent with units that are located in closets and the louvers are blocked, plugged or simply too small.
- The inducer blower wheel is loose, damaged or have dirt build up on the squirrel cage.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heating'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'A quick check for Inducer Motors:
Taking a current reading on the inducer motor can provide a quick look at how it is working.  If the motor is working properly it will typically pull 85% of FLA or higher.  If the flue is blocked or obstructed the motor will pull much lower amps.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heating'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Nuisance trips can occur for some of the following reasons:
- Low air flow caused by dirty filters.
- Dirty Blower Squirrel Cages.
- Closed Supply Air Registers.
- Low capacity Inducer Motor.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heating', 'Safety Devices'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Addressing Flame Rod (Flame Rectification) Issues:
- Verify that the flame rod is not damaged.
- Clean the flame rod if necessary.
- Verify the voltage to the flame rod, typically 80 to 120 vac to ground.
- Remember that the "Flame Rectification" allows a current path to ground.  If the burners are not properly grounded then the ground path for the flame current may not be completed. 
- The Flame Rectification current can be be measured with a Multimeter with a DC Micro Amp scale.  The meter would be in series with the Flame Rod.  Depending on the unit the reading should be between 1 and 10 microamps.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heating', 'Controls', 'Safety Devices'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Checking Gas Valves:
- With the wires disconnect and power off, read the continuity of the coil.
- Apply the specified voltage to the coil and the valve should open.
- It is not a good idea to take apart residential gas valves.
- If the valve has been exposed to water it should be replaced',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heating'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Furnace tripping on code for \'vent switch not closing or closed.\' Bluon gets a ton of calls for this issue. Typically the fix is related to a dirty tube, or bent tubes that trap water as well as rust in fitting.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heating', 'Controls', 'Fault Codes'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'Heater doesn\'t work after leaving site? Check connections and plugs for loose wires or corrosion.  Check the flame sensor and clean it.  Check your gas pressures, air flow switches and flame rod current.',
                'comment' => null,
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Heating'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'How can you tell if a HSI (hot surface ignitor) is bad?',
                'comment' => 'Resistance should be between 37-68 ohms. If the igniter and the surrounding air are at about 70°F and the igniter wires are not connected to any other electrical components, the resistance of the igniter should not exceed 75 ohms. If it does, the igniter should be replaced.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Igniters', 'Controls'],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'How do I know if a gas valve is faulty?',
                'comment' => 'Turn off power and remove terminal ends from gas valve. Set your multimeter to "mV." Hold each tester wand on the multimeter to one of the terminals on the gas valve. A normal reading will be in the range of 145 to 195 millivolts. Readings outside this range indicate the gas valve is defective and must be replaced. Furnace gas valves cannot be repaired.',
                'brand'   => null,
                'series'  => null,
                'issues'  => [],
                'more'    => [],
                'images'  => [],
            ],
            [
                'message' => 'What the heck is this resistor looking thing with a wire on each end and occasionally surrounded by clear plastic tubing near my burners?',
                'comment' => 'This is actually a fusible link that will open when exposed to heat over its rating (usually from flame rollout) and once opened will need to be replaced with one of the same rating once the issue that caused the failure is repaired.',
                'brand'   => null,
                'series'  => null,
                'issues'  => ['Safety Devices', 'Controls'],
                'more'    => [],
                'images'  => ['https://dl.airtable.com/.attachments/b776b015811d3c659b91a2e98b5e4e36/5cc018a7/Fusiblelink.PNG'],
            ],
            [
                'message' => 'Trane Precedent unit will not run in test mode but operates in normal conditions. If unit has a BMS system, the comm wires from BMS will need to be removed. This will allow for the unit to be put into test mode using normal test procedures.',
                'comment' => null,
                'brand'   => 'Trane',
                'series'  => 'Precedent',
                'issues'  => ['Controls'],
                'more'    => [],
                'images'  => [],
            ],
        ];
    }
}
