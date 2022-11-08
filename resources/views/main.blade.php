@extends('layout.app')

@section('title')
    Main
@endsection 

@section('content')
    <div class="main">
        <div class="left">
            <section class="recordARoll">
                <form action="{{ route('main') }}" method="post">
                    @csrf 

                    <fieldset>
                        <legend>Record a roll</legend>

                        <div class="flex">
                            <div class="form-group flex">
                                <label for="number">Number:</label>
                                <input type="number" class="form-control" min="0" max="36" name="number" id="number" value="" />
                            </div>

                            <div class="form-group">
                                <input type="hidden" name="action" value="recordARoll" aria-label="Action identifier" />

                                <input type="submit" class="btn btn-success" value="+" aria-label="Record a new roll" />
                            </div>
                        </div>
                    </fieldset>
                </form>

                <div class="messages">
                    @if (Session::has('msg'))
                        {{ Session::get('msg') }}
                    @endif 
                </div>
            </section>

            <section class="recommendations">
                <h2 class="center">
                    Recommendations
                </h2>

                <div>
                    <table class="table table-sm table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Count</th>
                                <th>Bet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($counts as $type => $count)
                                <tr>
                                    <td>{{ $type }}</td>
                                    <td>{{ $count }}</td>
                                    <td>
                                        @if ( $count >= 2 )
                                            <div class="btn custom-{{ $type }}">
                                                £{{ $stakes[$count - 1] ?? 'MAX' }}
                                            </div>
                                        @endif 
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>


                <div class="rouletteTable">
                    <div>
                        <h3>
                            Low
                        </h3>

                        @if ( $counts["low"] >= 2 )
                            <div class="btn tall custom custom-low">
                                £{{ $stakes[$counts["low"] - 1] ?? 'MAX' }}
                            </div>
                        @else 
                            <div class="tall">
                                0
                            </div>
                        @endif
                    </div>

                    <div>
                        <h3>
                            Even
                        </h3>

                        @if ( $counts["even"] >= 2 )
                            <div class="btn tall custom custom-even">
                                £{{ $stakes[$counts["even"] - 1] ?? 'MAX' }}
                            </div>
                        @else 
                            <div class="tall">
                                0
                            </div>
                        @endif
                    </div>

                    <div>
                        <h3>
                            Red
                        </h3>

                        @if ( $counts["red"] >= 2 )
                            <div class="btn tall custom custom-red">
                                £{{ $stakes[$counts["red"] - 1] ?? 'MAX' }}
                            </div>
                        @else 
                            <div class="tall">
                                0
                            </div>
                        @endif
                    </div>

                    <div>
                        <h3>
                            Black
                        </h3>

                        @if ( $counts["black"] >= 2 )
                            <div class="btn tall custom custom-black">
                                £{{ $stakes[$counts["black"] - 1] ?? 'MAX' }}
                            </div>
                        @else 
                            <div class="tall">
                                0
                            </div>
                        @endif
                    </div>

                    <div>
                        <h3>
                            Odd
                        </h3>

                        @if ( $counts["odd"] >= 2 )
                            <div class="btn tall custom custom-odd">
                                £{{ $stakes[$counts["odd"] - 1] ?? 'MAX' }}
                            </div>
                        @else 
                            <div class="tall">
                                0
                            </div>
                        @endif
                    </div>

                    <div>
                        <h3>
                            High
                        </h3>

                        @if ( $counts["high"] >= 2 )
                            <div class="btn tall custom custom-high">
                                £{{ $stakes[$counts["high"] - 1] ?? 'MAX' }}
                            </div>
                        @else 
                            <div class="tall">
                                0
                            </div>
                        @endif
                    </div>
                </div>


            </section>
        </div>

        <div class="right">
            <section class="historical">
                <div class="flex">
                    <h2 class="center">
                        Historical
                    </h2>

                    <form action="{{ route('main') }}" method="post">
                        @csrf 

                        <input type="hidden" name="action" value="clearAllHistorical" />
                        <input type="submit" class="btn btn-danger" value="Clear all" aria-label="Clear all historical rolls" />
                    </form>
                </div>

                <div class="tableContainer">
                    <small class="center">
                        (Most recent roll at the top)
                    </small>
                    
                    <table class="table table-sm table-hover table-striped">
                        <thead class="center">
                            <tr>
                                <th title="Number">Num.</th>
                                <th title="Red / Black">R/B</th>
                                <th title="Odd / Even">O/E</th>
                                <th title="High / Low">H/L</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="bold center">
                            @foreach ( $historicals as $historical )
                                <tr>
                                    <td>{{ $historical->num }}</td>
                                    
                                    <td style="color: {{ $historical->isRed  ? 'red' : 'black' }};">
                                        {{ $historical->isRed  ? 'Red' : 'Black' }}
                                    </td>

                                    <td style="color: {{ $historical->isEven ? 'darkorange' : 'purple' }};">
                                        {{ $historical->isEven ? 'Even' : 'Odd' }}
                                    </td>
                                    
                                    <td style="color: {{ $historical->isHigh ? 'green' : 'cyan' }};">
                                        {{ $historical->isHigh ? 'High' : 'Low' }}
                                    </td>
                                    
                                    <td>
                                        <form action="{{ route('main') }}" method="post">
                                            @csrf 

                                            <input type="hidden" name="id" value="{{ $historical->id }}" />
                                            <input type="hidden" name="action" value="deleteAHistorical" />
                                            <input type="submit" class="btn btn-warning" value="Delete" aria-label="Delete this roll" />
                                        </form>
                                    </td>
                                </tr>
                            @endforeach 
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

@endsection 
