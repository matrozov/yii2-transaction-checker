# Transaction checker

Component for checking the closing of transactions at the time of completion of work on a request.

Allows you to check for different database connections and for a variable number of events.
By default, the component checks upon the Application::EVENT_AFTER_REQUEST event,
as well as upon completion of the script, if it was stopped more rigidly.

## Installation
The preferred way to install this extension is through composer.

Either run
```shell script
php composer.phar require --prefer-dist matrozov/yii2-transaction-checker "*"
```

or add
```json
"matrozov/yii2-transaction-checker": "*"
```
to the require section of your composer.json file.

## Usage
Specify the component and point it to the bootstrap section of the framework.
```php
'bootstrap' => ['transactionChecker'],
'components' => [
    'transactionChecker' => [
        'class' => 'matrozov\yii2-transaction-checker\TransactionChecker',
    ],
],
```

You can directly specify the database components in which to check for
transaction completion in the component configuration.
By default, the component is "db".
```php
'connections' => ['db', 'db2'],
```

You can also use events in other components to control the completion of transactions in other cases. For example,
if the processing of an event from the queue is completed.
```php
'extendedEvents' => [
   'myComponent'  => 'final-work-event',
   'myComponent2' => ['final-work-event-1', 'final-work-event-2'],
],
```