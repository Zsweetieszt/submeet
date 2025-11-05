@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Dashboard</h4>
            </div>
            <div class="card-body px-4">
                <p>Hi {{ Auth::user()->given_name }} {{ Auth::user()->family_name }}! Welcome to {{ env('APP_NAME') }}.</p>
                <p>
                    SubMeet is a Conference Management System developed by
                    <strong>
                        <a href="#" role="button" data-coreui-toggle="modal" data-coreui-target="#jtkModal">
                            JTK POLBAN
                        </a>
                    </strong>.
                </p>
                <p>
                    The word "Sub" originates from the concept of substantial, which means something that carries weight,
                    value, and significance in its existence. Simultaneously, the word "Meet" represents the essence of
                    every conference, which is a gathering for collaboration.
                </p>
                <p>The name "SubMeet" ultimately becomes a symbol of harmony between substantial value and collaboration.
                </p>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="jtkModal" tabindex="-1" aria-labelledby="jtkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="jtkModalLabel">SubMeet Developers</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>
                        We, as part of
                        <a href="https://jtk.polban.ac.id" title="JTK POLBAN" target="_blank">JTK POLBAN</a>,
                        are pleased to acknowledge the developers whose dedication and expertise have greatly contributed to
                        the development of the Conference Management System SubMeet.
                    </p>
                    <ul class="list-unstyled mb-3">
                        <li><strong>Development Supervisor</strong>
                            <ul>
                                <li>Hashri Hayati</li>
                                <li>Muhammad Riza Alifi</li>
                                <li>Transmissia Semiawan</li>
                                <li>Djoko Cahyo Utomo Lieharyani</li>
                            </ul>
                        </li>
                        <li><strong>Main Developer</strong>
                            <ul>
                                <li>Thoriq Muhammad Fadhli</li>
                                <li>Faris Abulkhoir</li>
                                <li>Salsabil Khoirunisa</li>
                            </ul>
                        </li>
                        <li><strong>Support Developer</strong>
                            <ul>
                                <li>Ridho Sulistyo Saputro</li>
                                <li>Dinanda Khayra Hutama</li>
                                <li>Arman Yusuf Rifandi</li>
                                <li>Raihana Aisha Az-Zahra</li>
                            </ul>
                        </li>
                        <li><strong>Security Tester</strong>
                            <ul>
                                <li>Farrel Keiza Muhammad Yamin Putra</li>
                                <li>Muhammad Azharuddin Hamid</li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection