<?php

namespace App\Entity;

use App\Repository\SmartphoneRepository;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "smartphone",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 * )
 *
 */
#[ORM\Entity(repositoryClass: SmartphoneRepository::class)]
class Smartphone extends Product
{

}
