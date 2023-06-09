<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions">
<img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>

<a href="https://packagist.org/packages/laravel/framework">
<img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>

<a href="https://packagist.org/packages/laravel/framework">
<img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>

<a href="https://packagist.org/packages/laravel/framework">
<img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Introdução
- Trabalhando com filtros de dados de forma segura utilizando o eloquent
- Neste projetinho laravel, o objetivo é demonstrar a utilização do eloquenta com query, na busca de forma segura de registros 
em uma lista de usuários `não admin`.

##### Busca de usuários por `nome` e status não `admin`
- slq puro `SELECT * FROM users WHERE admin = false AND name LIKE '%rafael%'`

```
   User::query()
     ->where('admin', '=', false)
     ->where('name', 'like', '%'. request()->search . '%')
     ->get()
```

- Com está query conseguimos buscar todos usuários por nome e não `admin`.


##### Busca de usuários por `nome` e por `email`
- slq puro `SELECT * FROM users WHERE admin = false AND name LIKE '%rafael%' OR email LIKE '%blum%'`

```
   User::query()
     ->where('admin', '=', false)
     ->where('name', 'like', '%'. request()->search . '%')
     ->orWhere('email', 'like', '%'. request()->search . '%')
     ->get()
```

> Nesta busca conseguimos todos registros, `mas a query seleciona também usuários admin` -> **o que não pode!**.
> O problema desta expresão logica é que qualquer uma das três condições que resultar em verdadeiro, ele vai trazer. 
> Se nome diferente de falso, se nome conter a string OU se no email tiver a string.

- Para solucionar este problemas, é que NÃO podemos trazer não admins, 
então a condição `admin = false` é obrigatoria e o restante das condições devem estar entre parenteses.
- slq puro correto `SELECT * FROM users WHERE admin = false AND (name LIKE '%rafael%' OR email LIKE '%blum%')`

```
   User::query()
         ->where('admin', '=', false)
         ->where(function (Builder $query){
                   return $query
                          ->where('name', 'like', '%'. request()->search . '%')
                          ->orWhere('email', 'like', '%'. request()->search . '%');
         })->get()
```

- A função Builder do Eloquent vai retornar todos registro de cada expressão, assim simulando o `AND` e parenteses `( )`.

- Uma outra forma mais avançada utilizando o `when` e pegando pela `request` se exite dados no input `search`.

```
   User::query()
       ->where('admin', '=', false)
       ->when(\request()->filled('search'), function (Builder $q){
           return $q->where(function (Builder $query){
               return $query
                   ->where('name', 'like', '%'. request()->search . '%')
                   ->orWhere('email', 'like', '%'. request()->search . '%');
           });
       })
->get()
```

- Na query acima, caso o name `search` tenha sido preenchido, é verdadeiro e assim entra nas condições.

##### Utilizando um escopo local
> Os escopos locais permitem que você defina conjuntos comuns de restrições de consulta que podem ser facilmente 
> reutilizados em todo o aplicativo. Por exemplo, pode ser necessário recuperar com frequência todos os usuários 
> considerados `populares`. Para definir um escopo, prefixe um método de modelo Eloquent com `scope`.
  
- Os escopos sempre devem retornar a mesma instância do construtor de consultas ou `void`:

 ```
    public function scopeSearch(Builder $q, string $search)
    {
        return $q->where('name', 'like', '%'. request()->search . '%')
                 ->orWhere('email', 'like', '%'. request()->search . '%');
    }
 ```

- Uma vez definido o escopo, você pode chamar os métodos de escopo ao consultar o modelo. No entanto, você não deve 
 incluir o `scope` prefixo ao chamar o método. Você pode até mesmo encadear chamadas para vários escopos:

 ```
    User::query()
        ->where('admin', '=', false)
        ->when(
            \request()->filled('search'), fn(Builder $query) => $query->search(\request()->search)
        )
        ->get()
 ```


- E ainda existe `maneiras para simplificar` mais ainda o código.

 ```
    User::query()
        ->where('admin', '=', false)
        ->search(\request()->search)
        ->get()
 ```

- No modelo do usuário.

 ```
    public function scopeSearch(Builder $q, ?string $search)
    {
        return $q->when(
            str($search)->isNotEmpty(),
            fn(Builder $query) => $query->where('name', 'like', '%'. request()->search . '%')
                ->orWhere('email', 'like', '%'. request()->search . '%')
        );
    }
 ```

##### Ordenação

- Vamos ordenar por coluna recebida do request

 ```
    User::query()
        ->where('admin', '=', false)
        ->search(\request()->search)
            ->when(
                \request()->filled('column'),
                fn(Builder $q) => $q->orderBy(
                    \request()->column,
                    \request()->direction ? : 'ASC'
                )
            )->get()
 ```

- E no formulário adicionamos a seleceção do usuário na busca.

 ```
    <form action="{{route('dashboard')}}" method="post">
        @csrf

        <input type="text" id="first_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
            rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5
            dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
            dark:focus:border-blue-500" name="search" placeholder="Search users" value="{{old('search')}}">

        <div class="grid md:grid-cols-2 md:gap-6">
            <div>
                <label for="column" class="block mt-4 mb-2 text-sm font-medium text-gray-900 dark:text-white">Selecione tipo de busca</label>
                <select name="column" id="column" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option {{request()->column == '' ? 'selected':''}} value="">---</option>
                    <option {{request()->column == 'name' ? 'selected':''}} value="name">name</option>
                    <option {{request()->column == 'email' ? 'selected':''}} value="email">email</option>
                </select>
            </div>

            <div class="ml-4">
                <label for="direction" class="block mt-4 mb-2 text-sm font-medium text-gray-900 dark:text-white">Selecione tipo ordenação</label>
                <select name="direction" id="direction" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option {{request()->direction == 'ASC' ? 'selected':''}} value="ASC">ASC</option>
                    <option {{request()->direction == 'DESC' ? 'selected':''}} value="DESC">DESC</option>
                </select>
            </div>
        </div>


        <button type="submit" class="text-gray-900 p-2 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 w-full font-medium rounded-full text-sm px-5 py-2.5 mt-4 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800" style="background: rgb(102,201,136);">Buscar</button>
    </form>
 ```

> Ainda podemos validar os dados recebidos na request e protejer.

 ```
     $request->validate([
        'column'=>'in:name, ,email',
        'direction'=>'in:asc,desc'
    ]);
 ```

<p align="center">
	<a href="#"  target="_blank" title="Visit cardápio digital home page">
		<img src="image-dashboard.JPG" alt="filtro">
	</a>
</p>

### Contatos

Contatos 👇🏼 [rafaelblum_digital@hotmail.com]

[![Youtube Badge](https://img.shields.io/badge/-Youtube-FF0000?style=flat-square&labelColor=FF0000&logo=youtube&logoColor=white&link=https://www.youtube.com/channel/UCMvtn8HZ12Ud-sdkY5KzTog)](https://www.youtube.com/channel/UCMvtn8HZ12Ud-sdkY5KzTog)
[![Instagram Badge](https://img.shields.io/badge/-rafablum_-violet?style=flat-square&logo=Instagram&logoColor=white&link=https://www.instagram.com/rafablum_/)](https://www.instagram.com/rafablum_/)
[![Twitter: universoCode](https://img.shields.io/twitter/follow/universoCode?style=social)](https://twitter.com/universoCode)
[![Linkedin: RafaelBlum](https://img.shields.io/badge/-RafaelBlum-blue?style=flat-square&logo=Linkedin&logoColor=white&link=https://www.linkedin.com/in/rafael-blum-237133114/)](https://www.linkedin.com/in/rafael-blum-237133114/)
[![GitHub RafaelBlum](https://img.shields.io/github/followers/RafaelBlum?label=follow&style=social)](https://github.com/RafaelBlum)

<br/>


<img src="https://media.giphy.com/media/LnQjpWaON8nhr21vNW/giphy.gif" width="60"> <em><b>Adoro me conectar com pessoas diferentes,</b> então se você quiser dizer <b>oi, ficarei feliz em conhecê-lo mais!</b> :)</em>
