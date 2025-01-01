<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ServerController extends Controller
{
    public function getServers()
{
    try {
        $servers = DB::table('servers')
            ->orderBy('priority', 'desc')
            ->orderBy('server_id', 'desc')
            ->get()
            ->map(function($server) {
                return [
                    'ServerID' => (string)$server->server_id,
                    'Name' => $server->name,
                    'Type' => $server->type,
                    'Priority' => (string)$server->priority,
                    'IsActive' => (string)(bool)$server->is_active
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $servers
        ]);

    } catch (\Exception $error) {
        Log::error('Error getting servers: ' . $error->getMessage());
        return response()->json([
            'success' => false,
            'message' => $error->getMessage()
        ], 500);
    }
}

    public function addServer(Request $request)
    {
        try {
            $validated = $request->validate([
                'Name' => 'required|string',
                'Type' => 'required|string',
                'Priority' => 'integer|nullable',
                'IsActive' => 'boolean|nullable'
            ]);

            $serverId = DB::table('servers')->insertGetId([
                'name' => $validated['Name'],
                'type' => $validated['Type'],
                'priority' => $validated['Priority'] ?? 0,
                'is_active' => $validated['IsActive'] ?? true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thêm server thành công',
                'data' => [
                    'ServerId' => $serverId
                ]
            ]);

        } catch (\Exception $error) {
            Log::error('Error adding server: ' . $error->getMessage());
            return response()->json([
                'success' => false,
                'message' => $error->getMessage()
            ], 500);
        }
    }

    public function updateServer(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'Name' => 'required|string',
                'Type' => 'required|string',
                'Priority' => 'integer',
                'IsActive' => 'boolean'
            ]);

            DB::table('servers')
                ->where('server_id', $id)
                ->update([
                    'name' => $validated['Name'],
                    'type' => $validated['Type'],
                    'priority' => $validated['Priority'],
                    'is_active' => $validated['IsActive'],
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật server thành công'
            ]);

        } catch (\Exception $error) {
            Log::error('Error updating server: ' . $error->getMessage());
            return response()->json([
                'success' => false,
                'message' => $error->getMessage()
            ], 500);
        }
    }

    public function deleteServer($id)
    {
        try {
            $deleted = DB::table('servers')->where('server_id', $id)->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa server thành công'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Server không tồn tại'
            ], 404);

        } catch (\Exception $error) {
            Log::error('Error deleting server: ' . $error->getMessage());
            return response()->json([
                'success' => false,
                'message' => $error->getMessage()
            ], 500);
        }
    }
}
