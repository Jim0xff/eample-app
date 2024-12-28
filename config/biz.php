<?php
return [
    'topOfTheMoonTokens' =>
        explode(',',env('BIZ_TOP_OF_THE_MOON_TOKEN', '0xbe1bcaa424ce1a52c41a68b4804c59ad17036104')),
    'fundingGoalMetis'=> env('FUNDING_GOAL_METIS','20000000000000000000'),
    'bondingCurveAddress'=>  explode(',',env('BOUNDING_CURVE_ADDRESS', '0xe8385f3115f2aa17b1AB5B54508a41b834f7787b')),
];
