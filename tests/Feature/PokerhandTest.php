<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PokerhandTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_for_empty_board() {
        $this->json('POST', 'api/poker/evaluate-hand')
            ->assertStatus(400)
            ->assertJson([
                'code' => '400',
                'message' => 'Undefined index: board'
            ]);
    }

    public function test_for_invalid_hand_size() {
        $postData['board'][] = "3c";
        $postData['board'][] = "4c";
        $postData['board'][] = "5c";
        $postData['board'][] = "Kc";

        $this->json('POST', 'api/poker/evaluate-hand', $postData)
            ->assertStatus(400)
            ->assertJson([
                'code' => '400',
                'message' => 'Hand has to be 5 cards'
            ]);
    }

    public function test_for_duplicate_cards() {
        $postData['board'][] = "3c";
        $postData['board'][] = "4c";
        $postData['board'][] = "5c";
        $postData['board'][] = "5c";
        $postData['board'][] = "Kc";

        $this->json('POST', 'api/poker/evaluate-hand', $postData)
            ->assertStatus(400)
            ->assertJson([
                'code' => '400',
                'message' => 'Hand cannot have duplicate cards'
            ]);
    }

    public function test_for_invalid_card_format() {
        $postData['board'][] = "3c";
        $postData['board'][] = "4c";
        $postData['board'][] = "5c";
        $postData['board'][] = "5cd";
        $postData['board'][] = "Kc";

        $this->json('POST', 'api/poker/evaluate-hand', $postData)
            ->assertStatus(400)
            ->assertJson([
                'code' => '400',
                'message' => 'Card format is invalid: 5cd'
            ]);
    }

    public function test_for_straight_flush() {
        $postData['board'][] = "3c";
        $postData['board'][] = "4c";
        $postData['board'][] = "5c";
        $postData['board'][] = "6c";
        $postData['board'][] = "7c";

        $this->json('POST', 'api/poker/evaluate-hand', $postData)
            ->assertStatus(200)
            ->assertJson([
                'hand_value' => 'Straight Flush'
            ]);
    }

    public function test_for_four_of_a_kind() {
        $postData['board'][] = "3c";
        $postData['board'][] = "3d";
        $postData['board'][] = "3h";
        $postData['board'][] = "3s";
        $postData['board'][] = "7c";

        $this->json('POST', 'api/poker/evaluate-hand', $postData)
            ->assertStatus(200)
            ->assertJson([
                'hand_value' => 'Four of a Kind'
            ]);
    }

    public function test_for_full_house() {
        $postData['board'][] = "3c";
        $postData['board'][] = "3d";
        $postData['board'][] = "3h";
        $postData['board'][] = "7s";
        $postData['board'][] = "7c";

        $this->json('POST', 'api/poker/evaluate-hand', $postData)
            ->assertStatus(200)
            ->assertJson([
                'hand_value' => 'Full House'
            ]);
    }

    public function test_for_flush() {
        $postData['board'][] = "3c";
        $postData['board'][] = "8c";
        $postData['board'][] = "9c";
        $postData['board'][] = "10c";
        $postData['board'][] = "Ac";

        $this->json('POST', 'api/poker/evaluate-hand', $postData)
            ->assertStatus(200)
            ->assertJson([
                'hand_value' => 'Flush'
            ]);
    }

    public function test_for_straight() {
        $postData['board'][] = "6c";
        $postData['board'][] = "7h";
        $postData['board'][] = "8d";
        $postData['board'][] = "9s";
        $postData['board'][] = "10c";

        $this->json('POST', 'api/poker/evaluate-hand', $postData)
            ->assertStatus(200)
            ->assertJson([
                'hand_value' => 'Straight'
            ]);
    }

    public function test_for_three_of_a_kind() {
        $postData['board'][] = "6c";
        $postData['board'][] = "6h";
        $postData['board'][] = "6d";
        $postData['board'][] = "9s";
        $postData['board'][] = "10c";

        $this->json('POST', 'api/poker/evaluate-hand', $postData)
            ->assertStatus(200)
            ->assertJson([
                'hand_value' => 'Three of a Kind'
            ]);
    }

    public function test_for_two_pair() {
        $postData['board'][] = "6c";
        $postData['board'][] = "6h";
        $postData['board'][] = "10d";
        $postData['board'][] = "9s";
        $postData['board'][] = "10c";

        $this->json('POST', 'api/poker/evaluate-hand', $postData)
            ->assertStatus(200)
            ->assertJson([
                'hand_value' => 'Two pair'
            ]);
    }

    public function test_for_one_pair() {
        $postData['board'][] = "6c";
        $postData['board'][] = "8h";
        $postData['board'][] = "10d";
        $postData['board'][] = "9s";
        $postData['board'][] = "10c";

        $this->json('POST', 'api/poker/evaluate-hand', $postData)
            ->assertStatus(200)
            ->assertJson([
                'hand_value' => 'One pair'
            ]);
    }

    public function test_for_high_card() {
        $postData['board'][] = "6c";
        $postData['board'][] = "8h";
        $postData['board'][] = "Kd";
        $postData['board'][] = "9s";
        $postData['board'][] = "10c";

        $this->json('POST', 'api/poker/evaluate-hand', $postData)
            ->assertStatus(200)
            ->assertJson([
                'hand_value' => 'High Card'
            ]);
    }

}
