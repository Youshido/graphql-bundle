# Symfony GraphQl Bundle

### This is a bundle based on the pure [PHP GraphQL Server](http://github.com/youshido/graphql/) implementation

This bundle provides you with:

* Full compatibility with the [RFC Specification for GraphQL](https://facebook.github.io/graphql/)
* Agile object oriented structure to architect your GraphQL Schema
* Intuitive Type system that allows you to build your project much faster and stay consistent
* Built-in validation for the GraphQL Schema you develop
* Well documented classes with a lot of examples
* Automatically created endpoint /graphql to handle requests

**There are simple demo application to demonstrate how we build our API,
see [GraphQLDemoApp](https://github.com/Youshido/GraphQLDemoApp).**

## Table of Contents

* [Installation](#installation)
* [Symfony features included](#symfony-features-included)
    * [AbstractContainerAwareField class](#class-abstractcontainerawarefield)
    * [Service method as callable](#service-method-as-callable)
    * [Security](#security)
* [Documentation](#documentation)

## Installation

We assume you have `composer`, if you're not – install it from
the [official website](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx).  
If you need any help installing Symfony framework – here's the
link [http://symfony.com/doc/current/book/installation.html](http://symfony.com/doc/current/book/installation.html).
> Shortcut to install Symfony: `composer create-project symfony/framework-standard-edition my_project_name`

Once you have your composer up and running – you're ready to install the GraphQL Bundle.   
Go to your project folder and run:

```sh
composer require youshido/graphql-bundle
```

Then enable bundle in your `app/AppKernel.php`

```php
new Youshido\GraphQLBundle\GraphQLBundle(),
```

Add the routing reference to the `app/config/routing.yml`:

```yaml
graphql:
    resource: "@GraphQLBundle/Controller/"
```

or

```yaml
graphql:
    resource: "@GraphQLBundle/Resources/config/route.xml"
```

If you don't have a web server configured you can use a bundled version, simply run `php bin/console server:run`.

Let's check if you've done everything right so far – try to access url `localhost:8000/graphql`.  
You should get a JSON response with the following error:

```js
{"errors":[{"message":"Schema class does not exist"}]}
```

That's because there was no GraphQL Schema specified for the processor yet. You need to create a GraphQL Schema class
and set it inside your `app/config/config.yml` file.

> There is a way where you can use inline approach and do not create a Schema class, in order to do that you have to
> define your own GraphQL controller and use a `->setSchema` method of the processor to set the Schema.

The fastest way to create a Schema class is to use a generator shipped with this bundle:

```sh
php bin/console graphql:configure AppBundle
```

Here *AppBundle* is a name of the bundle where the class will be generated in.  
You will be requested for a confirmation to create a class.

After you've added parameters to the config file, try to access the following link in the
browser – `http://localhost:8000/graphql?query={hello(name:World)}`

> Alternatively, you can execute the same request using CURL client in your console  
> `curl http://localhost:8000/graphql --data "query={ hello(name: \"World\") }"`

Successful response from a test Schema will be displayed:

```js
{"data":{"hello":"world!"}}
```

That means you have GraphQL Bundle for the Symfony Framework configured and now can architect your GraphQL Schema:

Next step would be to link assets for GraphiQL Explorer by executing:

```sh
php bin/console assets:install --symlink
```

Now you can access it at `http://localhost:8000/graphql/explorer`

## Symfony features

### Class AbstractContainerAwareField:

AbstractContainerAwareField class used for auto passing container to field, add ability to use container in resolve
function:

```php
class RootDirField extends AbstractContainerAwareField
{

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return new StringType();
    }

    /**
     * @inheritdoc
     */
    public function resolve($value, array $args, ResolveInfo $info)
    {
        return $this->container->getParameter('kernel.root_dir');
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'rootDir';
    }
```

### Service method as callable:

Ability to pass service method as resolve callable:

```php
$config->addField(new Field([
    'name'    => 'cacheDir',
    'type'    => new StringType(),
    'resolve' => ['@resolve_service', 'getCacheDir']
]))
```

### Events:

You can use the Symfony Event Dispatcher to get control over specific events which happen when resolving graphql
queries.

```php
namespace ...\...\..;

use Youshido\GraphQL\Event\ResolveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MyGraphQLResolveEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'graphql.pre_resolve'  => 'onPreResolve',
            'graphql.post_resolve' => 'onPostResolve'
        ];
    }

    public function onPreResolve(ResolveEvent $event)
    {
		//$event->getFields / $event->getAstFields()..
    }

    public function onPostResolve(ResolveEvent $event)
    {
		//$event->getFields / $event->getAstFields()..
    }
}
```

#### Configuration

Now configure you subscriber so events will be caught. This can be done in Symfony by either XML, Yaml or PHP.

```xml
<service id="my_own_bundle.event_subscriber.my_graphql_resolve_event_subscriber" class="...\...\...\MyGraphQLResolveEventSubscriber">
	<tag name="graphql.event_subscriber" />
</service>
```

### Security:

Bundle provides two ways to guard your application: using black/white operation list or using security voter.

#### Black/white list

Used to guard some root operations. To enable it you need to write following in your config.yml file:

```yaml
graphql:

  #...

  security:
    black_list: ['hello'] # or white_list: ['hello']

```

#### Using security voter:

Used to guard any field resolve and support two types of guards: root operation and any other field resolving (including
internal fields, scalar type fields, root operations). To guard root operation with your specified logic you need to
enable it in configuration and use  `SecurityManagerInterface::RESOLVE_ROOT_OPERATION_ATTRIBUTE` attribute. The same
things need to do to enable field guard, but in this case use `SecurityManagerInterface::RESOLVE_FIELD_ATTRIBUTE`
attribute.
[Official documentation](http://symfony.com/doc/current/security/voters.html) about voters.

> Note: Enabling field security lead to a significant reduction in performance

Config example:

```yaml
graphql:
    security:
        guard:
            field: true # for any field security
            operation: true # for root level security
```

Voter example (add in to your `services.yml` file with tag `security.voter`):

```php
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQLBundle\Security\Manager\SecurityManagerInterface;

class GraphQLVoter extends Voter
{

    /**
     * @inheritdoc
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [SecurityManagerInterface::RESOLVE_FIELD_ATTRIBUTE, SecurityManagerInterface::RESOLVE_ROOT_OPERATION_ATTRIBUTE]);
    }

    /**
     * @inheritdoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        // your own validation logic here

        if (SecurityManagerInterface::RESOLVE_FIELD_ATTRIBUTE == $attribute) {
            /** @var $subject ResolveInfo */
            if ($subject->getField()->getName() == 'hello') {
                return false;
            }

            return true;
        } elseif (SecurityManagerInterface::RESOLVE_ROOT_OPERATION_ATTRIBUTE == $attribute) {
            /** @var $subject Query */
            if ($subject->getName() == '__schema') {
                return true;
            }
        }
    }
}
```

## GraphiQL extension:

To run [graphiql extension](https://github.com/graphql/graphiql) just try to access
to `http://your_domain/graphql/explorer`

## Documentation

All detailed documentation is available on the main GraphQL repository –http://github.com/youshido/graphql/.
