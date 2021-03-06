<?php
namespace App\Http\Controllers\Adm;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdufrgsNoAr;
use Classes\Helpers;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as Image;

class AdufrgsNoArController extends Controller
{
    private $msgErros = '';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $id = null)
    {
      $return = ['title' => 'Adufrgs no Ar'];
      $erros = false;
      $firstCall = true;

      if ($request->isMethod('post'))
      {
          $firstCall = false;
          $validator = $this->validator($request);

          if (!$validator->fails())
          {
              switch ( $request->input('action') )
              {
                  case 'register':
                      $this->register($request);
                      break;
                  case 'edit':
                      $this->edit($request);
                      break;
                  case 'order':
                      $this->order($request);
                      break;
                  case 'delete':
                      $this->delete($request);
                      break;
              }
          }
          else
          {
            $erros = true;
          }
      }

      $registers = new AdufrgsNoAr();
      $categoriaAll = $registers->all()->sortByDesc('order'); //Retorna todos registros
      $return['registers'] = $categoriaAll;



      if($erros)
      {
        return view('adm.adufrgs-no-ar')->withReturn($return)->withErrors($validator);
      }
      else
      {
        if($firstCall) {
            return view('adm.adufrgs-no-ar')->withReturn($return);
        } else {
            return redirect('/adm/adufrgs-no-ar'); //Adicionado o redirect para limpar o post
        }
      }

    }


    private function validator($request)
    {
        switch ( $request->input('action') )
        {
            case 'register':
                $rules = [
                    'title' => 'required|max:240',
                    'idVideo' => 'required|max:240'
                ];
                $messages = [
                    'title.required' => 'Campo t??tulo ?? obrigat??rio.',
                    'title.max' => 'Campo t??tulo n??o pode ter mais do que 240 caracteres.',
                    'idVideo.required' => 'Campo Id do v??deo ?? obrigat??rio.',
                    'idVideo.max' => 'Campo Id do v??deo n??o pode ter mais do que 240 caracteres.'
                ];
                break;
            case 'edit':
                $rules = [
                    'id' => 'required',
                    'title' => 'required|max:240',
                    'idVideo' => 'required|max:240',
                ];
                $messages = [
                    'id.required' => 'Nenhum registro informado!',
                    'title.required' => 'Campo t??tulo ?? obrigat??rio.',
                    'title.max' => 'Campo t??tulo n??o pode ter mais do que 240 caracteres.',
                    'idVideo.required' => 'Campo Id do v??deo ?? obrigat??rio.',
                    'idVideo.max' => 'Campo Id do v??deo n??o pode ter mais do que 240 caracteres.',
                ];
                break;
            case 'order':
                $rules = [
                  'id' => 'required',
                ];
                $messages = [
                    'id.required' => 'Nenhum registro informado!',
                ];
                break;
            case 'delete':
                $rules = [
                  'id' => 'required',
                ];
                $messages = [
                  'id.required' => 'Nenhum registro informado!',
                ];
                break;
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        return $validator;
    }


    private function register(Request $request)
    {
        $register = new AdufrgsNoAr();
        $id = 0;
        if ($register->max('id')) {
            $id = $register->max('id');
        }
        $id++;

        $register->title = $request->input('title');
        $register->order = $id;
        $register->description = $request->input('description');
        $register->id_video = $request->input('idVideo');
        $link = '<iframe width="640" height="360" src="https://www.youtube.com/embed/'.$register->id_video.'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
        $register->link = $link;

        $register->save();

        return true;
    }

    private function order(Request $request)
    {
        $update = new AdufrgsNoAr();
        $edit = $update->all()->sortByDesc('order')->toArray();
        $orderNext = null;

        if( count($edit) > 1 )
        {
            for($i = 0; $i < count($edit); $i++)
            {
                if(isset($edit[$i+1]))
                {
                    $orderNext = $edit[$i+1]['order'];

                    $next = $update->find($edit[$i+1]['id']);
                    $next->order = $edit[$i]['order'];
                    $next->save();

                    $atual = $update->find($edit[$i]['id']);
                    $atual->order = $orderNext;
                    $atual->save();
                    return true;
                }
            }
        }

        return true;
    }

    private function edit(Request $request)
    {
        $register = new AdufrgsNoAr();
        $edit = $register->find($request->input('id'));

        $edit->title = $request->input('title');
        $edit->description = $request->input('description');
        $edit->id_video = $request->input('idVideo');
        $link = '<iframe width="640" height="360" src="https://www.youtube.com/embed/'.$edit->id_video.'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
        $edit->link = $link;

        $edit->save();

        return true;
    }

    private function delete(Request $request)
    {
        $tvAdverso = new AdufrgsNoAr();
        $delete = $tvAdverso->find($request->input('id'));
        $delete->delete();

        return true;
    }
}
