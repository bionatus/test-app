<?php

namespace App\Services\Nutshell;

use \NutshellApi;

class Nutshell
{
    /**
     * The Nutshell API instance.
     *
     * @type \NutshellApi
     */
    protected $api;

    /**
     * Constructor.
     *
     * @param  \NutshellApi  $api
     * @return void
     */
    public function __construct(NutshellApi $api) {
        $this->api = $api;
    }

    public function createContact($user) {
        $username = $user->first_name . ' ' . $user->last_name;

        $newContact = $this->api->call('newContact', array(
            'contact' => array(
                'name'  => $username,
                'phone' => $user->phone,
                'email' => $user->email,
                'address' => array(
                    'main' => array(
                        'address_1'  => $user->address,
                        'city'       => $user->city,
                        'state'      => $user->state,
                        'postalCode' => $user->zip,
                        'country'    => $user->country,
                    )
                ),
                'customFields' => array(
                    'Trained By:'                => 'Website Accreditation',
                    'How did you hear of Bluon?' => $user->references,
                    'Preferred Supplier #1'      => $user->hvac_supplier,
                    'Service Manager Name' => $user->service_manager_name,
                    'Service Manager Phone' => $user->service_manager_phone,
                    'Which service apps do you use?' => $user->apps,
                    'Accreditation' => $user->accreditated ? 'Tier 1' : 'No',
                )
            ),
        ));

        return $newContact->id;
    }

    function createAccount($contactId, $user) {
        $industries = [
            'residential' => 'We do mostly Residential',
            'commercial' => 'We do mostly Commercial',
            'other' => 'Other/NA',
        ];

        $number_of_employees = [
            '1-10' => 'We have 1-10 employees',
            '10-30' => 'We have 10-30 employees',
            '30-100' => 'We have 30-100 employees',
            '100+' => 'We have 100+ employees',
        ];

        if (empty($user->company)) {
            return;
        }

        $args = [
            'account' => [
                'name'     => $user->company,
                'contacts' => [
                    [
                        'id'           => $contactId,
                        'relationship' => $user->occupation,
                    ],
                ],

                'IndustryName' => !empty($user->type_of_services) ? $industries[$user->type_of_services] : 'Other/NA',
                'customFields' => [
                    '# of HVAC techs employed:' => $user->techs_number,
                ],
            ],
        ];

        if (!empty($user->employees)) {
            $args['account']['customFields']['# of employees:'] = $number_of_employees[$user->employees];
        }

        $this->api->newAccount($args);
    }

    function getContactByEmail($email) {
        $userData = $this->getUserDataByEmail($email);

        if (empty($userData)) {
            return;
        }

        $contact = $this->api->call('getContact', array(
            'contactId' => $userData->id,
        ));

        return $contact;
    }

    function getUserDataByEmail($email) {
        $results = $this->api->call('searchByEmail', array(
            'emailAddressString' => $email,
        ));

        return !empty($results->contacts[0]) ? $results->contacts[0] : null;
    }

    function setUserAccreditation($contact, $accreditation = 'No', $date = null) {
        $this->api->call('editContact', array(
            'contactId' => $contact->id,
            'rev' => $contact->rev,
            'contact' => array(
                'customFields' => array(
                    'Accreditation'              => $accreditation,
                    'Tier 1 Accreditation Date'  => $date,
                )
            ),
        ));
    }

    function changeUserAccreditation($user, $accreditation = 'No') {
        try {
            $nutshellContact = $this->getContactByEmail($user->email);

            if ($nutshellContact) {
                $this->setUserAccreditation($nutshellContact, $accreditation, $user->accreditated_at);
            }
        } catch (Exception $e) {
            return response()->json('Could not update accreditation.', 401);
        }
    }
}
