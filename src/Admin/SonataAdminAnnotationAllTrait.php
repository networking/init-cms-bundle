<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Admin;

trait SonataAdminAnnotationAllTrait
{
    use SonataAdminAnnotationReaderTrait;
    use SonataAdminAnnotationDatagridMapperTrait;
    use SonataAdminAnnotationFormMapperTrait;
    use SonataAdminAnnotationShowMapperTrait;
    use SonataAdminAnnotationListMapperTrait;
}
