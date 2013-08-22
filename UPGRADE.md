Upgrade
=======

### 2013-08-22

The new ContentRoute templateName member variable allows us to us the same html template multiple times but with
varying configuration. for example you could use the same template "AcmeBundle:Default:two_column.html.twig" but define
different restricted types or different controllers.

!IMPORTANT! Back up your DB before going any further.
- Run the command php app/console doctrine:schema:update --dump-sql / --force to add the new field template_name to the
  content_route table
- Manually add the key value which corresponds to the template stored in the template field in the template_name field
  for each entry in the content_route table:

E.G If the following is my template config, the template name will be "acme_two_column_template"

'acme_two_column_template':
    template: "AcmeBundle:Default:two_column.html.twig"
    name: "Standard Inhaltseite"
    icon: "bundles/sandboxinitcms/img/template_header_two_column.png"
    controller: "NetworkingInitCmsBundle:FrontendPage:index"
    zones:
        - { name: background_image, span: 12, restricted_types: ['Sandbox\InitCmsBundle\Entity\PageBackgroundImage'], max_content_items: 1}
        - { name: header, span: 12, restricted_types: ['Networking\ElasticSearchBundle\Entity\SearchableText'], max_content_items: 1}
        - { name: left , span:8}
        - { name: right , span:4}