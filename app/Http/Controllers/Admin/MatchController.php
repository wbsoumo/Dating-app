<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Swipe;
use App\Models\UserMatch;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'active');

        $query = UserMatch::with([
            'user1.photos', 'user1.profile', 'user1.location',
            'user2.photos', 'user2.profile', 'user2.location',
            'unmatchedByUser',
        ])->latest('matched_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $matches = $query->paginate(25)->withQueryString();

        return view('admin.matches.index', compact('matches', 'status'));
    }

    public function swipes(Request $request)
    {
        $type = $request->input('type', 'all');

        $query = Swipe::with(['user.photos', 'targetUser.photos'])
            ->latest('id');

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $swipes = $query->paginate(30)->withQueryString();

        return view('admin.matches.swipes', compact('swipes', 'type'));
    }
}
