# poker-evaluator
Demo project for evaluating poker hands

Runs on the Laravel 8.83.27 framework using a React.js frontend

Requirements: PHP, Composer, NPM

To install, run these commands in the root folder:

composer install
npm install

You may have to run "npm run watch" in order to build the frontend

Then run "php artisan serve"

You should then be able to access the app on: http://127.0.0.1:8000/

Select a card's suit and value from the dropdowns, then press Add card to add it to your hand. Press Clear Hand to remove all cards. Press Evaluate to get the value of the hand

In order to run the feature tests, run "php artisan test"

The evaluator is also available as an API endpoint on: "http://127.0.0.1:8000/api/poker/evaluate-hand"

It takes a JSON post in the body with a format such as this:

{
     "board": [
         "Ac",
         "4h",
         "5d",
         "6c",
         "Ks"
     ]
 }

The first part is the value of each card (A, 2-10, J, Q, K) and the second is the suit (c, h, d, s)
