<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(WhatsAppAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
    {
        $days = $request->get('days', 30);
        
        $peakHours = $this->analyticsService->getPeakMessagingHours($days);
        // Format the hours for the view (add :00 suffix for display)
        $formattedPeakHours = $peakHours->map(function ($item) {
            $item->hour_label = $item->hour . ':00';
            return $item;
        });
        
        $peakDays = $this->analyticsService->getPeakMessagingDays($days);
        // Format the day names for the view
        $formattedPeakDays = $peakDays->map(function ($item) {
            $item->day_name = $this->getDayName($item->day_of_week);
            return $item;
        });
        
        $dailyVolume = $this->analyticsService->getDailyMessageVolume($days);
        // Format the dates for the view
        $formattedDailyVolume = $dailyVolume->map(function ($item) {
            $item->date_formatted = $item->date;
            return $item;
        });
        
        $messageTypes = $this->analyticsService->getMessageTypeDistribution($days);
        // Format type names for the view
        $formattedMessageTypes = $messageTypes->map(function ($item) {
            $item->type_display = $item->type ?: 'Unknown';
            return $item;
        });
        
        $data = [
            'mostContactedUsers' => $this->analyticsService->getMostContactedUsers(10, $days),
            'mostRepliedUsers' => $this->analyticsService->getMostRepliedUsers(10, $days),
            'peakHours' => $formattedPeakHours,
            'peakDays' => $formattedPeakDays,
            'activeTimePeriods' => $this->analyticsService->getActiveTimePeriods($days),
            'dailyVolume' => $formattedDailyVolume,
            'messageTypes' => $formattedMessageTypes,
        ];

        return view('analytics', $data);
    }

    public function apiData(Request $request)
    {
        $days = $request->get('days', 30);
        
        return response()->json([
            'mostContactedUsers' => $this->analyticsService->getMostContactedUsers(10, $days),
            'peakHours' => $this->analyticsService->getPeakMessagingHours($days),
            'peakDays' => $this->analyticsService->getPeakMessagingDays($days),
            'dailyVolume' => $this->analyticsService->getDailyMessageVolume($days),
        ]);
    }

    private function getDayName($dayIndex)
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return $days[$dayIndex - 1] ?? 'Unknown';
    }
}