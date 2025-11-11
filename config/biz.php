<?php
return [
    'topOfTheMoonTokens' =>
        explode(',',env('BIZ_TOP_OF_THE_MOON_TOKEN', '0xbe1bcaa424ce1a52c41a68b4804c59ad17036104')),
    'fundingGoalMetis'=> env('FUNDING_GOAL_METIS','20000000000000000000'),
    'bondingCurveAddress'=>  explode(',',env('BOUNDING_CURVE_ADDRESS', '0xE1eDE24167A7abbEEae3d362373f7937aEa8767a')),
    'netSwapToolAddress' =>  explode(',',env('NET_SWAP_TOOL_ADDRESS', '0xe85a8EA5E4A066305c3e1E52A52C6f61D8Eb5dDd')),
];
