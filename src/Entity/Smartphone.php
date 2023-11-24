<?php

namespace App\Entity;

use App\Repository\SmartphoneRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\AssociationOverride;
use Doctrine\ORM\Mapping\AssociationOverrides;

#[ORM\Entity(repositoryClass: SmartphoneRepository::class)]
class Smartphone extends Product
{

}
