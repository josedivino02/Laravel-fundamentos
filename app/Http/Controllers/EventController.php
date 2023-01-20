<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;

class EventController extends Controller
{

//----------------------------------------------------------------------------------------------------------------------------------------------
    public function index()
    {
        $search = request('search');

        if ($search) {

            $events = Event::where([
                ['title', 'like', '%' .$search .'%']
            ])->get();

        } else {
            $events = Event::all();
        }

        return view('main', ['events' => $events, 'search' => $search]);
    }

//----------------------------------------------------------------------------------------------------------------------------------------------
    public function create()
    {
        return view('events.create');
    }

//----------------------------------------------------------------------------------------------------------------------------------------------
    public function store(Request $request)
    {
        $event = new Event();

        $event->title = $request->title;
        $event->date = $request->date;
        $event->city = $request->city;
        $event->private = $request->private;
        $event->description = $request->description;
        $event->items = $request->items;

        //Img - verifica se existe uma input tipo image e valida a imagem
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $requestImage = $request->image;

            $extension = $requestImage->extension(); // extensão do arquivo

            $imageName = md5($requestImage->getClientOriginalName() .strtotime("now")) ."." .$extension; //hash do arquivo

            $requestImage->move(public_path('img/events'), $imageName); //salvando a imagem no diretorio

            $event->image = $imageName;
        }

        // usuario logado
        $user = auth()->user();
        $event->user_id = $user->id;


        $event->save();

        return redirect('/')->with('msg', 'Evento criado com sucesso!');
    }

//----------------------------------------------------------------------------------------------------------------------------------------------
    public function show($id)
    {
        $user = auth()->user();

        $hasUserJoined = false;

        if ($user) {
            $userEvents = $user->eventsAsParticipant->toArray();

            foreach ($userEvents as $userEvent) {
                if ($userEvent['id'] == $id) {
                    $hasUserJoined = true;
                }
            }
        }

        $event = Event::findOrFail($id);

        $eventOwner = User::where('id', $event->user_id)->first()->toArray();

        return view('events.show', ['event' => $event, 'eventOwner' => $eventOwner, 'hasUserJoined' => $hasUserJoined ]);
    }

//----------------------------------------------------------------------------------------------------------------------------------------------
    public function dashboard()
    {
        $user = auth()->user();

        $events = $user->events; //events metodo que esta na models - eventos do usuario

        $eventsAsParticipant = $user->eventsAsParticipant;

        return view('events.dashboard', ['events' => $events, 'eventsAsParticipant' => $eventsAsParticipant]);
    }

//----------------------------------------------------------------------------------------------------------------------------------------------
    public function destroy($id)
    {
        Event::findOrFail($id)->delete();

        return redirect('/dashboard')->with('msg', 'Evento excluído com sucesso!');
    }

//----------------------------------------------------------------------------------------------------------------------------------------------
    public function edit($id)
    {
        $user = auth()->user();

        $event = Event::findOrFail($id);

        if ($user->id != $event->user_id) {
            return redirect('/dashboard');
        }

        return view('events.edit', ['event' => $event]);
    }

//----------------------------------------------------------------------------------------------------------------------------------------------
    public function update(Request $request)
    {
        $data = $request->all();

        //Img - verifica se existe uma input tipo image e valida a imagem
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $requestImage = $request->image;

            $extension = $requestImage->extension(); // extensão do arquivo

            $imageName = md5($requestImage->getClientOriginalName() .strtotime("now")) ."." .$extension; //hash do arquivo

            $requestImage->move(public_path('img/events'), $imageName); //salvando a imagem no diretorio

            $data['image'] = $imageName;
        }

        Event::findOrFail($request->id)->update($data);

        return redirect('/dashboard')->with('msg', 'Evento atualizado com sucesso!');
    }

//----------------------------------------------------------------------------------------------------------------------------------------------
    public function joinEvent($id)
    {
        $user = auth()->user();

        $user->eventsAsParticipant()->attach($id); //attach faz a ligação

        $event = Event::findOrFail($id);

        return back()->with('msg', 'Sua presença está confirmado no evento: ' .$event->title);
    }

//----------------------------------------------------------------------------------------------------------------------------------------------
    public function leaveEvent($id)
    {
        $user = auth()->user();

        $user->eventsAsParticipant()->detach($id); //detach faz a remoção

        $event = Event::findOrFail($id);

        return redirect('/dashboard')->with('msg', 'Você saiu do evento: ' .$event->title);

    }
}
