<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\Participation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormationController extends Controller
{
    public function get_all_formations()
    {
        $formations = Formation::all();

        if (!$formations) {
            return response([
                "error" => true,
                "message" => "No formations found",
            ], 404);
        }

        return response([
            "error" => false,
            "formations" => $formations,
        ], 200);
    }

    public function get_user_formations(string $user_id)
    {
        $formations = User::find($user_id)->formations;

        if (!$formations) {
            return response([
                "error" => true,
                "message" => "No formations found",
            ], 404);
        }

        return response([
            "error" => false,
            "formations" => $formations,
        ], 200);
    }

    public function create_formation(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'duration' => 'required',
            'image' => 'required',
        ]);

        $user_id = $request->input("user_id");
        if (!$user_id) {
            return response([
                "error" => true,
                "message" => "User id was not provided",
            ], 400);
        }

        $data['user_id'] = $user_id;
        $data['name'] = $request->input('name');
        $data['description'] = $request->input('description');
        $data['price'] = $request->input('price');
        $data['duration'] = $request->input('duration');
        $data['image'] = $request->input('image');

        $formation = Formation::create($data);

        if (!$formation) {
            return response([
                "error" => true,
                "message" => "Formation not created",
            ], 400);
        }

        return response([
            "error" => false,
            "message" => "Formation created successfully",
        ], 201);
    }


    public function participate_in_formation(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'formation_id' => 'required',
        ]);

        $user_id = $request->input("user_id");
        $formation_id = $request->input("formation_id");

        $user = User::find($user_id);
        $formation = Formation::find($formation_id);

        if (!$user) {
            return response([
                "error" => true,
                "message" => "User not found",
            ], 404);
        }

        if (!$formation) {
            return response([
                "error" => true,
                "message" => "Formation not found",
            ], 404);
        }

        $data['user_id'] = $user_id;
        $data['formation_id'] = $formation_id;

        Participation::create($data);

        return response([
            "error" => false,
            "message" => "User participated in formation successfully",
        ], 201);
    }


    public function get_all_formation_participants(string $id)
    {
        $formation = Formation::find($id);

        if (!$formation) {
            return response([
                "error" => true,
                "message" => "Formation not found",
            ], 404);
        }

        $participants = DB::table("formation_participants")->selectRaw("*")->where("formation_id", $id)->get();
        
        $users = [];

        foreach ($participants as $participant) {
            $user = User::find($participant->user_id);
            array_push($users, $user);
        }

        return response([
            "error" => false,
            "participants" => $users,
        ], 200);
    }

    public function delete_participant($id) {
        $participant = Participation::find($id);

        if (!$participant) {
            return response([
                "error" => true,
                "message" => "Participant not found",
            ], 404);
        }

        $participant->delete();

        return response([
            "error" => false,
            "message" => "Participant deleted successfully",
        ], 200);
    }
}
