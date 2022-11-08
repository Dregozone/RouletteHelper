<!DOCTYPE html>
<html lang="en">
    <head>
        <title>@yield('title') - Roulette Helper</title>

        {{-- Bootstrap  --}}
        <link 
            rel="stylesheet" 
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" 
            integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" 
            crossorigin="anonymous"
        />

        {{-- Custom page CSS --}}
        @if ( file_exists("css/{$page}.css") )
            <link 
                rel="stylesheet" 
                href="{{ asset("css/{$page}.css") }}" 
            />
        @endif 

    </head>
    <body>
        @yield('content')
    </body>
</html>
