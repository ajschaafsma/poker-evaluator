<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PokerController extends Controller
{
    public function evaluateHand(Request $request) {
        try {
            $board = request()->post();

            //In a larger application, I would move much of this logic into a separate model
            //The model would have methods for validating the hand size, duplicates etc
            //As well as the logic for evaluating the value
            //For example $hand->checkValidity();
            //and $hand->evaluateValue();
            $handArray = [];

            if(!is_null($board['board']) && !empty($board['board'])) {

                if (sizeof($board['board']) != 5) {
                    throw new \Exception("Hand has to be 5 cards");
                }

                if ($board['board'] != array_unique($board['board'])) {
                    throw new \Exception("Hand cannot have duplicate cards");
                }

                //Map card strings to array
                foreach ($board['board'] as $card) {
                    //Hacky way of dealing with 10 being the only value with two digits
                    //Should rather extend the regex to handle this case as well
                    $card = str_replace("10", "T", $card);
                    //Regex to check card format validity
                    //Matches a two character string where the first character has to be the value and the second the suit
                    if (preg_match('/^[2-9TAJQK]{1}[cshd]{1}$/', $card)) {
                        $suit = substr($card, -1);
                        $value = substr($card, 0, 1);
                        //Map to numbers to make it easier to calculate straights
                        if ($value == 'A') {
                            $value = 1;
                        }
                        if ($value == 'T') {
                            $value = 10;
                        }
                        if ($value == 'J') {
                            $value = 11;
                        }
                        if ($value == 'Q') {
                            $value = 12;
                        }
                        if ($value == 'K') {
                            $value = 13;
                        }
                        $newcard['value'] = $value;
                        $newcard['suit'] = $suit;
                        $handArray[] = $newcard;
                    } else {
                        throw new \Exception("Card format is invalid: ".$card);
                    }
                }

                //Get an array of just the suits
                $suits = array_column($handArray, 'suit');

                //If all elements in the suit array are the same, then we have a flush
                if (count(array_unique($suits)) == 1) {
                    $isFlush = true;
                } else {
                    $isFlush = false;
                }

                //Get array of values
                $values = array_column($handArray, 'value');

                //This counts how many of each value there are
                //Used further down to calculate pairs, 3s, 4s, etc
                $countValues = array_count_values($values);

                //This evaluates if the values are consecutive
                //See https://stackoverflow.com/a/71750631
                //Checks if we have a straight
                $range = range(min($values), max($values));
                $diff = array_diff($range, $values);
                $isStraight = empty($diff) && count($values) === count($range);

                //TODO: Cater for royal flush
                //TODO: Cater for 10-J-Q-K-A straight

                //Check in order of hierarchy - omitting 5 of a kind
                if ($isStraight && $isFlush) {
                    $handValue = 'Straight Flush';
                } else if (array_search(4, $countValues)) {
                    //If the value 4 is found when counting value occurrences
                    $handValue = 'Four of a Kind';
                } else if (array_search(3, $countValues) && array_search(2, $countValues)) {
                    //If one value occurs 3 times while another occurs 2 times
                    $handValue = 'Full House';
                } else if ($isFlush) {
                    $handValue = 'Flush';
                } else if ($isStraight) {
                    $handValue = 'Straight';
                } else if (array_search(3, $countValues)) {
                    //If the value 3 is found when counting value occurrences
                    $handValue = 'Three of a Kind';
                } else if (array_search(2, array_count_values($countValues))) {
                    //If two different pairs of same values were found
                    $handValue = 'Two pair';
                } else if (array_search(2, $countValues)) {
                    //If the value 2 is found when counting value occurrences
                    $handValue = 'One pair';
                } else {
                    $handValue = 'High Card';
                }

                return response()->json([
                    "hand_value" => $handValue
                ], 200);

            } else {
                throw new \Exception("board object is empty");
            }

        } catch (\Exception $e) {
            //In a more robust application, I would use Laravel's built in exception handlers
            return response()->json([
                "message" => $e->getMessage(),
                "code" => 400
            ], 400);
        }

    }
}

