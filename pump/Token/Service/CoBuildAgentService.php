<?php

namespace Pump\Token\Service;

use App\InternalServices\OpenLaunchChatService\OpenLaunchChatService;

class CoBuildAgentService
{
   public function sendChats($app, $agentId, $content)
   {
       /** @var OpenLaunchChatService $openLaunchChatService */
       $openLaunchChatService = resolve('open_launch_chat_service');
       $rt = $openLaunchChatService->chatPost("co-build-agent/chat.json", ['app'=>$app, 'outAgentId'=>$agentId,'content'=>$content],[], true);

       return $rt;
   }

   public function chatList($app, $agentId, $pageSize, $pageNum)
   {
       /** @var OpenLaunchChatService $openLaunchChatService */
       $openLaunchChatService = resolve('open_launch_chat_service');
       $rt = $openLaunchChatService->chatGet("co-build-agent/chatList.json", ['app'=>$app, 'outAgentId'=>$agentId, 'pageNum'=>$pageNum, 'pageSize'=>$pageSize], [], true);

       return $rt;
   }

   public function contributeData($app, $agentId, $data)
   {
       /** @var OpenLaunchChatService $openLaunchChatService */
       $openLaunchChatService = resolve('open_launch_chat_service');
       $rt = $openLaunchChatService->chatPost("co-build-agent/contribute.json", ['app'=>$app, 'outAgentId'=>$agentId, 'data'=>$data], [], true);
   }

   public function myContributeData($app, $agentId)
   {
       /** @var OpenLaunchChatService $openLaunchChatService */
       $openLaunchChatService = resolve('open_launch_chat_service');
       $rt = $openLaunchChatService->chatGet("co-build-agent/myContributes.json", ['app'=>$app, 'outAgentId'=>$agentId], [], true);
       return $rt;
   }
}
