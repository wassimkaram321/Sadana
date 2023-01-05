@extends('layouts.back-end.app')
@section('title', \App\CPU\translate('Plans List'))
<meta name="csrf-token" content="{{ csrf_token() }}">
@push('css_or_js')

<style>
    .kanban-heading {
        display: flex;
        flex-direction: row;
        width: 100%;
        justify-content: center;
        font-family: sans-serif;
    }



    .kanban-board {
        padding: 10px;
        display: flex;
        flex-wrap: wrap;
        flex-direction: row;
        gap: 15px;
        /* justify-content: space-between; */
        font-family: sans-serif;
        border-color: #041562
    }


    .kanban-heading-text {
        font-size: 1rem;
        background-color: rgba(189, 189, 189, 0.5);
        padding: 0.8rem 1.7rem;
        border-radius: 0.5rem;
        margin: 1rem;
        height: 1%;
        width: 100%;
    }



    .kanban-block {
        border-color: #041562;
        background-color: white;
        box-shadow: 0px 0px 25px -2px rgba(189, 189, 189, 0.5);
        padding: 0.6rem;
        min-width: 32%;
        /* min-width: 250px; */
        /* min-width: 14rem; */
        height: 300px;
        /* max-height: 100%; */
        border-radius: 0.3rem;
        overflow-y: scroll;
    }

    .create-new-task-block {

        background-color: #00c9a7;
        padding: 0.6rem;
        min-width: 250px;
        /* min-width: 14rem; */
        height: 300px;
        /* max-height: 100%; */
        border-radius: 0.3rem;
        overflow-y: scroll;
    }

    .kanban-block strong {
        border-radius: 10px;
        margin-bottom: 15px;
        background-color: #f1f1fc;
        color: #041562;
        font-size: 17px;
        padding: 0.35rem;
        /* min-width: 14rem; */
        height: 40px;
        width: 100%;
        display: block;
        text-align: center;
    }

    .row {
        margin-right: 0px !important;
        margin-left: 0px !important;
        /* gap: 10px */
    }

    #todo {
        /* background-color: #fec6d1; */
    }

    #inprogress {
        /* background-color: #ffaf00; */
        /* display: none; */
    }

    #done {
        /* background-color: #678b67; */
        /* display: none; */
    }

    .task {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background-color: white;
        margin: 0.2rem 0rem 0.4rem 0rem;
        border: 1px solid #e1e1fa;
        box-shadow: 0px 0px 25px -5px #e9e9fc;
        FONT-WEIGHT: bold;
        font-family: sans-serif;
        padding: 8px 5px;
        transition: all 0.3s ease-in-out;
        cursor: pointer;
        position: relative;
    }

    .task a {
        color: gray;
    }

    .task:hover {
        transform: scale(1.08)
    }

    #task-button {
        margin: 0.2rem 0rem 0.1rem 0rem;
        background-color: white;
        border-radius: 0.2rem;
        width: 100%;
        border: 0.25rem solid black;
        padding: 0.5rem 2.7rem;
        border-radius: 0.3rem;
        font-size: 1rem;
    }

    .create-new-task-block {
        display: none;
        /* display: flex; */
        background: #ffaf00;
        width: 64.4%;
        flex-direction: column;
    }

    .form-row {
        display: flex;
        flex-direction: row;
        margin: 0.2rem;
    }

    .form-row-label {
        width: 15%;
        padding: 0.2rem;
        padding-right: 0.5rem;
        border: 0.1rem solid black;
        border-right: 0;
        border-radius: 0.2rem 0rem 0rem 0.2rem;
    }

    .form-row-input {
        border: 0.1rem solid black;
        border-radius: 0rem 0.2rem 0.2rem 0rem;
        width: 85%;
    }

    textarea {
        resize: none;
    }

    .form-row-buttons {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        margin: 0.2rem;
    }

    .phar_name {
        text-align: center
    }

    #edit-button,
    #save-button,
    #cancel-button {
        margin: 0.2rem 0rem 0.1rem 0rem;
        background-color: white;
        border-radius: 0.2rem;
        width: 49.2%;
        border: 0.25rem solid black;
        padding: 0.5rem 2.7rem;
        border-radius: 0.3rem;
        font-size: 1rem;
    }

    #edit-button {
        display: none;
    }

</style>
@endpush


@section('content')


<div>
    <div class="kanban-heading" style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
        <span class="kanban-heading-text task">{{\App\CPU\translate('Begin_Plan')}}&nbsp;&nbsp;:&nbsp;&nbsp;{{$begin}}</span>
        <span class="kanban-heading-text task">{{\App\CPU\translate('End_Plan')}}&nbsp;&nbsp;:&nbsp;&nbsp;{{$end}}</span>
    </div>


    <div class="kanban-board">

        <div class="kanban-block" id="pharmacies" ondrop="drop(event,id)" ondragover="allowDrop(event)">
            <strong>{{\App\CPU\translate('Pharmacies')}}</strong>
            @foreach ($pharmacies as $pharmacy)
            <div class="task" id={{$pharmacy->id}} draggable="true" ondragstart="drag(event)">
                <span style="">{{$pharmacy->name}}</span>
            </div>
            @endforeach

        </div>

        @foreach ($periods as $period)

        @php
        $date= $period->format('Y-m-d');
        $d= new \DateTime($date);
        @endphp

        <div class="kanban-block" id={{$period->format('Y-m-d')}} ondrop="drop(event,id)" ondragover="allowDrop(event)">
            <strong>{{$period->format('Y-m-d')}}&nbsp;&nbsp;&nbsp;{{\App\CPU\translate($d->format('l'))}}</strong>

            @php
            $pharmaciesSelectedTasks = \App\Model\WorkPlanTask::where([['task_plan_id','=',$plan_id],['task_date','=',$period->format('Y-m-d')]])->get(['pharmacy_id']);
            $pharmaciesTask = \App\Pharmacy::whereIn('id', $pharmaciesSelectedTasks)->get();
            @endphp
            @foreach ($pharmaciesTask as $pharmacyTask)
            <div class="task" id={{$pharmacyTask->id}} draggable="true" ondragstart="drag(event)">
                <span style="">{{$pharmacyTask->name}}</span>
            </div>
            @endforeach
        </div>
        @endforeach

    </div>
</div>



@endsection


<script>
    function drag(ev) {
        ev.dataTransfer.setData("text", ev.target.id);
    }


    function allowDrop(ev) {
        ev.preventDefault();
    }


    function drop(ev, task_date) {
        ev.preventDefault();
        var pharmacy_id = ev.dataTransfer.getData("text");
        ev.currentTarget.appendChild(document.getElementById(pharmacy_id));

        var plan_id = {{json_encode($plan_id)}};

        $("document").ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: '/admin/sales-man/work-plan/task/store/' + plan_id
                , type: "POST"
                , dataType: "json"
                , data: {
                    pharmacy_id: pharmacy_id
                    , task_date: task_date
                }
                , success: function(data) {
                    //console.log(data.success);
                }
            });

        });
    }

</script>

@push('script')



@endpush
