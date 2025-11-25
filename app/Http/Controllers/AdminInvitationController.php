<?php

namespace App\Http\Controllers;

use App\Mail\AdminInvitationMail;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class AdminInvitationController extends Controller
{
    public function index()
    {
        // Acceso ya controlado por middleware de rol en rutas
        $invitations = Invitation::latest()->get();
        return Inertia::render('admin/Invitations', [
            'invitations' => $invitations->map(fn($i)=>[
                'id'=>$i->id,
                'name'=>$i->name,
                'email'=>$i->email,
                'role'=>$i->role,
                'used_at'=>$i->used_at,
                'expires_at'=>$i->expires_at,
                'created_at'=>$i->created_at,
            ])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|email|max:255',
            'role'=>'required|in:Administrador general,Administrador de unidad,Administrador academico',
            'days'=>'nullable|integer|min:1|max:30'
        ]);

        $token = Str::uuid()->toString();
        $expires = $request->filled('days') ? now()->addDays((int)$request->days) : null;

        $invitation = Invitation::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'role'=>$request->role,
            'token'=>$token,
            'expires_at'=>$expires
        ]);

        Mail::to($invitation->email)->send(new AdminInvitationMail($invitation));

        return back()->with('success','Invitación enviada');
    }

    public function resend(Invitation $invitation)
    {
        if($invitation->isUsed()) {
            return back()->with('error','Ya utilizada');
        }
        Mail::to($invitation->email)->send(new AdminInvitationMail($invitation));
        return back()->with('success','Invitación reenviada');
    }

    public function destroy(Invitation $invitation)
    {
        $invitation->delete();
        return back()->with('success','Invitación eliminada');
    }
}
