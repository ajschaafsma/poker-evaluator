<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PokerController extends Controller
{
    public function createHand() {

    }

    public function evaluateHand(Request $request) {
        //Post hand in JSON array format. Example: [{"value":"1","suit":"C"},{"value":"5","suit":"C"},{"value":"5","suit":"H"},{"value":"J","suit":"D"},{"value":"K","suit":"S"}]

        $board = request()->post();

        $handArray = [];

        foreach($board['board'] as $card) {
            $suit = substr($card, -1);
            //Compensation for a 10
            if(strlen($card) == 3) {
                $value = substr($card, 0, 2);
            } else {
                $value = substr($card, 0, 1);
            }
            //Map to numbers to make it easier to calculate straights
            if($value == 'A') {
                $value = 1;
            }
            if($value == 'J') {
                $value = 11;
            }
            if($value == 'Q') {
                $value = 12;
            }
            if($value == 'K') {
                $value = 13;
            }
            $newcard['value'] = $value;
            $newcard['suit'] = $suit;
            $handArray[] = $newcard;
        }

        //Get an array of just the suits
        $suits = array_column($handArray, 'suit');

        //If all elements in the suit array are the same, then we have a flush
        //If not, we do not have a flush
        if(count(array_unique($suits)) == 1 ) {
            $isFlush = true;
        } else {
            $isFlush = false;
        }

        //Get array of values
        $values = array_column($handArray, 'value');

        $min = min($values);
        $max = max($values);

        $countValues = array_count_values($values);

        //This evaluates if the values are consecutive
        //See https://stackoverflow.com/a/71750631
        //Checks if we have a straight
        $range = range(min($values), max($values));
        $diff = array_diff($range, $values);
        $isStraight = empty($diff) && count($values) === count($range);

        //Check in order of hierarchy - omitting 5 of a kind
        if($isStraight && $isFlush) {
            $handValue = 'Straight Flush';
        } else if(array_search(4, $countValues)) {
            //If the value 4 is found when counting value occurences
            $handValue = 'Four of a Kind';
        } else if(array_search(3, $countValues) && array_search(2, $countValues)) {
            //If one value occurs 3 times while another occurs 2 times
            $handValue = 'Full House';
        } else if($isFlush) {
            $handValue = 'Flush';
        } else if($isStraight) {
            $handValue = 'Straight';
        } else if(array_search(3, $countValues)) {
            //If the value 3 is found when counting value occurences
            $handValue = 'Three of a Kind';
        } else if(array_search(2, array_count_values($countValues))) {
            //If two different pairs of same values were found
            $handValue = 'Two pair';
        } else if(array_search(2, $countValues)) {
            //If the value 2 is found when counting value occurences
            $handValue = 'One pair';
        } else {
            $handValue = 'High Card';
        }

        return response()->json([
            "hand_value" => $handValue
        ], 200);

    }
}

