Custom User Settings
====================

If you would like the CMS user to be able to customise his/her experience in the backend, then the User Settings object is the thing to use.This Object is not persisted to its own DB Table, but rather saved as an object in a field in the User Object.

This allows very flexible user settings that can be completely different for each setup. Just call the method setAdminSetting or getAdminSetting on the current user to set and retrieve whatever it is that you need.

Here is an example from the MenuItemAdminContoller:

```
if ($this->getRequest()->get('show_now_confirm_dialog')) {
    $user = $this->getUser();
    $user->setAdminSetting('menuAdmin.show_now_confirm_dialog', true);

    $em = $this->getDoctrine()->getManager();

    $em->persist($user);
    $em->flush();
}
```

Of course you can retrieve settings in your templates as well:

```
{% if not app.user.getAdminSetting('menuAdmin.show_now_confirm_dialog') %}
show this
{% endif %}
```