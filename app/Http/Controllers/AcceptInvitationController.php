<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class AcceptInvitationController extends Controller
{
    public function show($token)
    {
        $invitation = Invitation::where('token',$token)->first();
        if(!$invitation || $invitation->isUsed() || $invitation->isExpired()) {
            return Inertia::render('auth/InvalidInvitation');
        }
        return Inertia::render('auth/AcceptInvitation',[
            'token'=>$token,
            'email'=>$invitation->email,
            'name'=>$invitation->name,
            'role'=>$invitation->role,
            'expires_at'=>$invitation->expires_at
        ]);
    }

    public function store(Request $request, $token)
    {
        $invitation = Invitation::where('token',$token)->first();
        if(!$invitation || $invitation->isUsed() || $invitation->isExpired()) {
            return back()->withErrors(['token'=>'Invitación inválida']);
        }

        $request->validate([
            'password'=>'required|confirmed|min:8'
        ]);

        $user = User::firstOrCreate([
            'email'=>$invitation->email
        ],[
            'name'=>$invitation->name,
            'password'=>Hash::make($request->password)
        ]);

        if(!$user->hasRole($invitation->role)) {
            $user->assignRole($invitation->role);
        }

        $invitation->update(['used_at'=>now()]);

        return redirect()->route('login')->with('success','Cuenta activada, inicia sesión.');
    }
}
