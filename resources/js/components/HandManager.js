import React from 'react';
import ReactDOM from 'react-dom';
import Deck from "react-poker";
import "react-poker/dist/styles.css"
import Dropdown from 'react-dropdown';
import 'react-dropdown/style.css';
import axios from "axios";
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

/*See https://www.npmjs.com/package/react-poker for base of this component */

const range = (start, count) =>
    Array.apply(0, Array(count)).map((element, index) => {
        return index + start;
    });

function shuffle(array) {
    const copy = [];
    let n = array.length;
    let i;
    // While there remain elements to shuffle…
    while (n) {
        // Pick a remaining element…
        i = Math.floor(Math.random() * array.length);

        // If not already shuffled, move it to the new array.
        if (i in array) {
            copy.push(array[i]);
            delete array[i];
            n--;
        }
    }

    return copy;
}

const suits = ["d", "c", "h", "s"];
const displaysuits = [
    {value: "d", label: "Diamonds"},
    {value: "c", label: "Clubs"},
    {value: "h", label: "Hearts"},
    {value: "s", label: "Spades"}
]
const ranks = [
    "A",
    "2",
    "3",
    "4",
    "5",
    "6",
    "7",
    "8",
    "9",
    "10",
    "J",
    "Q",
    "K"
];

const getDeck = () =>
    shuffle(
        ranks
            .map(r => suits.map(s => r + s))
            .reduce((prev, curr) => prev.concat(curr))
    );

class HandManager extends React.Component {
    constructor(props) {
        super(props);
        this.state = { board: [], deck: getDeck(), evaluation: "", selectedSuit: null, selectedValue: null };
        this.randomHand = this.randomHand.bind(this);
        this.evaluateHand = this.evaluateHand.bind(this);
        this.chooseCard = this.chooseCard.bind(this);
    }

    newRound() {
        const { deck, board } = this.state;

        const newDeck = getDeck();
        this.setState(Object.assign({}, { board: [], deck: newDeck }));
    }

    dealFlop() {
        const { deck, board } = this.state;
        const flop = range(0, 5).map(e => deck.pop());

        this.setState(Object.assign({}, { board: flop, deck }));
    }

    dealCard() {
        const { deck, board } = this.state;
        const card = deck.pop();

        this.setState(Object.assign({}, { deck, board: board.concat(card) }));
    }

    randomHand() {
        const { deck, board } = this.state;

        this.setState({
            evaluation: ""
        })

        const newDeck = getDeck();
        this.setState(Object.assign({}, { board: [], deck: newDeck }));

        const flop = range(0, 5).map(e => deck.pop());
    }

    chooseCard() {
        const { deck, board, selectedSuit, selectedValue } = this.state;
        if(selectedSuit && selectedValue && board.length !== 5) {
            let cardString = selectedValue+selectedSuit;
            if(!board.includes(cardString)) {
                this.setState(Object.assign({}, {deck, board: board.concat(cardString)}));
            }
        }
    }

    handleSuitChange = (option) => {
        this.setState({
            selectedSuit: option.value
        })
    }

    handleValueChange = (option) => {
        this.setState({
            selectedValue: option.value
        })
    }

    evaluateHand() {
        const { deck, board } = this.state;
        let postArray = new Array();
        axios.post('/api/poker/evaluate-hand', {
            board
        }).then((response) => {
            if(response.data.error) {
                toast("Error: "+response.data.error);
            } else if(response.data.hand_value) {
                this.setState({
                    evaluation: response.data.hand_value
                })
            }
        }).catch(function (error) {
            if (error.response) {
                if(error.response.data.message) {
                    toast("Error: "+error.response.data.message);
                }
            } else if (error.request) {
                toast("An unknown error occurred");
            } else {
                toast("An unknown error occurred");
            }
        })
    }

    render() {
        const { board, evaluation } = this.state;

        return (
            <div style={{ left: "10vw", top: "10vh", position: "absolute" }}>
                <div>
                    <ToastContainer />
                    <Dropdown options={displaysuits} onChange={this.handleSuitChange} placeholder="Select a suit" />
                    <Dropdown options={ranks} onChange={this.handleValueChange} placeholder="Select a value" />
                    <button
                        style={{ padding: "1.5em", margin: "2em" }}
                        onClick={() => this.chooseCard()}
                    >
                        Add card
                    </button>
                        <button
                            style={{ padding: "1.5em", margin: "2em" }}
                            onClick={() => this.newRound()}
                        >
                            Clear Hand
                        </button>
                        </div>
                    <div>
                        <button
                            style={{ padding: "1.5em", margin: "2em" }}
                            onClick={this.evaluateHand}
                        >
                            Evaluate
                        </button>
                        Hand evaluation: {evaluation}
                    </div>
                    <Deck
                        board={board}
                        boardXoffset={375} // X axis pixel offset for dealing board
                        boardYoffset={200} // Y axis pixel offset for dealing board
                        size={200} // card height in pixels
                    />
            </div>
        );
    }
}

export default HandManager;



if (document.getElementById('hand-manager')) {
    ReactDOM.render(<HandManager />, document.getElementById('hand-manager'));
}

