<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Data\ORM;

use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractEmailFixture;

class LoadAssignmentTutorGroupEmailTemplate extends AbstractEmailFixture
{
    /**
     * Return path to email templates
     *
     * @return string
     */
    public function getEmailsDir(): string
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@TeachersAssignmentBundle/Migrations/Data/ORM/emails/assignment_tutor_group');
    }
}
