<?php

namespace App\Services;

use App\Models\WhatsappMessage;
use App\Models\ChatUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WhatsAppAnalyticsService
{
    /**
     * Get users you text the most (most contacted)
     */
    public function getMostContactedUsers($limit = 10, $days = 30)
    {
        $businessPhoneId = config('services.whatsapp.business_phone_id', env('WHATSAPP_BUSINESS_PHONE_ID'));
        return ChatUser::select('chat_users.*', DB::raw('COUNT(whatsapp_messages.id) as message_count'))
            ->join('whatsapp_messages', 'chat_users.id', '=', 'whatsapp_messages.chat_user_id')
            ->whereBetween('whatsapp_messages.timestamp', [
                Carbon::now()->subDays($days),
                Carbon::now()
            ])
            ->where('whatsapp_messages.from', '!=', $businessPhoneId)
            ->groupBy('chat_users.id')
            ->orderByDesc('message_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get users you receive messages from the most
     */
    public function getMostRepliedUsers($limit = 10, $days = 30)
    {
        $businessPhoneId = config('services.whatsapp.business_phone_id', env('WHATSAPP_BUSINESS_PHONE_ID'));
        return ChatUser::select('chat_users.*', DB::raw('COUNT(whatsapp_messages.id) as message_count'))
            ->join('whatsapp_messages', 'chat_users.id', '=', 'whatsapp_messages.chat_user_id')
            ->whereBetween('whatsapp_messages.timestamp', [
                Carbon::now()->subDays($days),
                Carbon::now()
            ])
            ->where('whatsapp_messages.from', '!=', $businessPhoneId)
            ->groupBy('chat_users.id')
            ->orderByDesc('message_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get peak messaging time by hour
     */
    public function getPeakMessagingHours($days = 30)
    {
        return WhatsAppMessage::select(DB::raw('HOUR(timestamp) as hour'), DB::raw('COUNT(*) as count'))
            ->whereBetween('timestamp', [
                Carbon::now()->subDays($days)->startOfDay(),
                Carbon::now()->endOfDay()
            ])
            ->groupBy(DB::raw('HOUR(timestamp)'))
            ->orderBy('hour')
            ->get();
    }

    /**
     * Get peak messaging time by day of week
     */
    public function getPeakMessagingDays($days = 30)
    {
        return WhatsAppMessage::select(DB::raw('DAYOFWEEK(timestamp) as day_of_week'), DB::raw('COUNT(*) as count'))
            ->whereBetween('timestamp', [
                Carbon::now()->subDays($days)->startOfDay(),
                Carbon::now()->endOfDay()
            ])
            ->groupBy(DB::raw('DAYOFWEEK(timestamp)'))
            ->orderBy('day_of_week')
            ->get();
    }

    /**
     * Get most active time periods
     */
    public function getActiveTimePeriods($days = 30)
    {
        return WhatsAppMessage::select(
                DB::raw('HOUR(timestamp) as hour'),
                DB::raw('DAYOFWEEK(timestamp) as day_of_week'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('timestamp', [
                Carbon::now()->subDays($days)->startOfDay(),
                Carbon::now()->endOfDay()
            ])
            ->groupBy(DB::raw('HOUR(timestamp)'), DB::raw('DAYOFWEEK(timestamp)'))
            ->orderByDesc('count')
            ->limit(10)
            ->get();
    }

    /**
     * Get message volume by date
     */
    public function getDailyMessageVolume($days = 30)
    {
        $businessPhoneId = config('services.whatsapp.business_phone_id', env('WHATSAPP_BUSINESS_PHONE_ID'));
        $startDate = Carbon::now()->subDays($days)->startOfDay()->format('Y-m-d H:i:s');
        $endDate = Carbon::now()->endOfDay()->format('Y-m-d H:i:s');
        
        return WhatsAppMessage::select(
                DB::raw('DATE(timestamp) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw("SUM(CASE WHEN `from` = '" . $businessPhoneId . "' THEN 1 ELSE 0 END) as sent_count"),
                DB::raw("SUM(CASE WHEN `from` != '" . $businessPhoneId . "' THEN 1 ELSE 0 END) as received_count")
            )
            ->whereBetween('timestamp', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(timestamp)'))
            ->orderBy('date')
            ->get();
    }

    /**
     * Get message type distribution
     */
    public function getMessageTypeDistribution($days = 30)
    {
        return WhatsAppMessage::select('type', DB::raw('COUNT(*) as count'))
            ->whereBetween('timestamp', [
                Carbon::now()->subDays($days)->startOfDay(),
                Carbon::now()->endOfDay()
            ])
            ->groupBy('type')
            ->get();
    }
}