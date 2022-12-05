@extends('layouts.app')
@section('content')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h2>{{$titleOfIndexPage}}
        <span>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route($createURL) }}">
                <i class="bi bi-file-earmark-plus-fill"></i> Tambah
            </a>
        </span>
    </h2>
</div>
<div class="card">
    @csrf
    <x-viho::data-table :model="$modelRecords" :showButton="$showURL" :editButton="$editURL" :deleteButton="$deleteURL"/>
</div>
{{-- @push('js') --}}

<script type="text/javascript">
    /*
    DELETE
    */
    function destroy(action,id=''){
        var isConfirm=confirm('anda yakin menghapus');
        var csrfToken=$("input[name='_token']").val();
        if(isConfirm){

            $.post(action, {'_method':'delete','_token':csrfToken},function( data ) {
                document.location.href='{{route($indexURL)}}';
            },'json');
        }
    }

</script>
{{-- @endpush --}}
@endsection
