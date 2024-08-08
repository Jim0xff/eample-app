<?php

return [

    "Raffle" => json_decode(
        '[{
		"inputs": [{
				"internalType": "uint256",
				"name": "entranceFee",
				"type": "uint256"
			},
			{
				"internalType": "address",
				"name": "vrfCoordinatorV2",
				"type": "address"
			},
			{
				"internalType": "bytes32",
				"name": "gasLane",
				"type": "bytes32"
			},
			{
				"internalType": "uint16",
				"name": "subscriptionId",
				"type": "uint16"
			},
			{
				"internalType": "uint32",
				"name": "callbackGasLimit",
				"type": "uint32"
			},
			{
				"internalType": "uint256",
				"name": "interval",
				"type": "uint256"
			}
		],
		"stateMutability": "nonpayable",
		"type": "constructor"
	},
	{
		"inputs": [{
				"internalType": "address",
				"name": "have",
				"type": "address"
			},
			{
				"internalType": "address",
				"name": "want",
				"type": "address"
			}
		],
		"name": "OnlyCoordinatorCanFulfill",
		"type": "error"
	},
	{
		"inputs": [],
		"name": "Raffle_NotEnoughETHEntered",
		"type": "error"
	},
	{
		"inputs": [{
				"internalType": "uint256",
				"name": "currentBalance",
				"type": "uint256"
			},
			{
				"internalType": "uint256",
				"name": "players",
				"type": "uint256"
			},
			{
				"internalType": "uint256",
				"name": "state",
				"type": "uint256"
			}
		],
		"name": "Raffle_UpkeepNotNeed",
		"type": "error"
	},
	{
		"inputs": [],
		"name": "Raflle_NotOpen",
		"type": "error"
	},
	{
		"inputs": [],
		"name": "Transfer_Failed",
		"type": "error"
	},
	{
		"anonymous": false,
		"inputs": [{
				"indexed": true,
				"internalType": "address",
				"name": "player",
				"type": "address"
			},
			{
				"indexed": false,
				"internalType": "uint256",
				"name": "amount",
				"type": "uint256"
			},
			{
				"indexed": true,
				"internalType": "uint256",
				"name": "roundId",
				"type": "uint256"
			}
		],
		"name": "RaffleEnter",
		"type": "event"
	},
	{
		"anonymous": false,
		"inputs": [{
			"indexed": true,
			"internalType": "uint256",
			"name": "requestId",
			"type": "uint256"
		}],
		"name": "RequestedRaffleWinner",
		"type": "event"
	},
	{
		"anonymous": false,
		"inputs": [{
			"indexed": true,
			"internalType": "uint256",
			"name": "roundId",
			"type": "uint256"
		}],
		"name": "StartNewRound",
		"type": "event"
	},
	{
		"anonymous": false,
		"inputs": [{
				"indexed": true,
				"internalType": "address",
				"name": "winner",
				"type": "address"
			},
			{
				"indexed": true,
				"internalType": "uint256",
				"name": "roundId",
				"type": "uint256"
			}
		],
		"name": "WinnerPicked",
		"type": "event"
	},
	{
		"inputs": [{
			"internalType": "bytes",
			"name": "checkData",
			"type": "bytes"
		}],
		"name": "checkUpkeep",
		"outputs": [{
				"internalType": "bool",
				"name": "upkeepNeeded",
				"type": "bool"
			},
			{
				"internalType": "bytes",
				"name": "performData",
				"type": "bytes"
			}
		],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "enterRaffle",
		"outputs": [],
		"stateMutability": "payable",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "getBalance",
		"outputs": [{
			"internalType": "uint256",
			"name": "",
			"type": "uint256"
		}],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "getCurrentInterval",
		"outputs": [{
			"internalType": "uint256",
			"name": "",
			"type": "uint256"
		}],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "getEntranceFee",
		"outputs": [{
			"internalType": "uint256",
			"name": "",
			"type": "uint256"
		}],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "getGasLane",
		"outputs": [{
			"internalType": "bytes32",
			"name": "",
			"type": "bytes32"
		}],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "getGasLimit",
		"outputs": [{
			"internalType": "uint32",
			"name": "",
			"type": "uint32"
		}],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [{
			"internalType": "uint256",
			"name": "index",
			"type": "uint256"
		}],
		"name": "getPlayer",
		"outputs": [{
			"internalType": "address",
			"name": "",
			"type": "address"
		}],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "getRaffleState",
		"outputs": [{
			"internalType": "enum Raffle.RaffleState",
			"name": "",
			"type": "uint8"
		}],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "getRecentWinner",
		"outputs": [{
			"internalType": "address",
			"name": "",
			"type": "address"
		}],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "getRoundId",
		"outputs": [{
			"internalType": "uint256",
			"name": "",
			"type": "uint256"
		}],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "getSubscriptionId",
		"outputs": [{
			"internalType": "uint64",
			"name": "",
			"type": "uint64"
		}],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [{
			"internalType": "bytes",
			"name": "performData",
			"type": "bytes"
		}],
		"name": "performUpkeep",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [{
				"internalType": "uint256",
				"name": "requestId",
				"type": "uint256"
			},
			{
				"internalType": "uint256[]",
				"name": "randomWords",
				"type": "uint256[]"
			}
		],
		"name": "rawFulfillRandomWords",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [{
			"internalType": "enum Raffle.RaffleState",
			"name": "_state",
			"type": "uint8"
		}],
		"name": "setRaffleState",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	}
]', 1
    ),

];
