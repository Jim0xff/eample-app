<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pump\Token\Service\CoBuildAgentService;

class CoBuildAgentController extends Controller
{
    public function sendChat(Request $request){
        /** @var CoBuildAgentService $coBuildAgentService */
        $coBuildAgentService = resolve('co_build_agent_service');
        $params = $request->all();
        $rt = $coBuildAgentService->sendChats($params['app'], $params['agentId'], $params['content']);
        return response()->json(['data' => $rt, 'code' => 200]);
    }

    public function chatList(Request $request){
        /** @var CoBuildAgentService $coBuildAgentService */
        $coBuildAgentService = resolve('co_build_agent_service');
        $params = $request->all();
        $rt = $coBuildAgentService->chatList($params['app'], $params['agentId'], $params['pageSize'], $params['pageNum']);
        return response()->json(['data' => $rt, 'code' => 200]);
    }

    public function contributeData(Request $request){
        /** @var CoBuildAgentService $coBuildAgentService */
        $coBuildAgentService = resolve('co_build_agent_service');
        $params = $request->all();
        $coBuildAgentService->contributeData($params['app'], $params['agentId'], $params['data']);
        return response()->json(['data' => true, 'code' => 200]);
    }

    public function myContributeData(Request $request){
        /** @var CoBuildAgentService $coBuildAgentService */
        $coBuildAgentService = resolve('co_build_agent_service');
        $params = $request->all();
        $rt = $coBuildAgentService->myContributeData($params['app'], $params['agentId']);
        return response()->json(['data' => $rt, 'code' => 200]);
    }

}
