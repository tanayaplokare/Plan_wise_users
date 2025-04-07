<div class="nk-sidebar">
    <div class="nk-nav-scroll">
        <ul class="metismenu" id="menu">
            <li class="nav-label">Main</li>
            <li>
                <a href="{{ route('dashboard') }}"> {{-- Assuming you have a named route 'dashboard' --}}
                    <i class="mdi mdi-view-dashboard"></i> <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <li>
                <a class="has-arrow" href="#" aria-expanded="false">
                    <i class="mdi mdi-web"></i><span class="nav-text">Domain Data</span>
                </a>
                <ul aria-expanded="false">
                    <li>
                        <a href="{{ url('/planuploads') }}">
                            <?php
                            if(auth()->user()->role === 'admin') {
                                $labelDomainData = "Upload";
                            }else{
                                $labelDomainData = "Download" ;
                            }
                            ?>
                          <span class="nav-text">{{ $labelDomainData}}</span>
                         </a>
                    </li>

                    <li>
                        <a href="{{ url('/plans') }}"> {{-- Or use route('plans.index') if you have it --}}
                           <span class="nav-text">Plans</span>
                         </a>
                    </li>
                    <li>
                        <a href="{{ route('users.index') }}"> {{-- Assuming named route 'users.index' --}}
                            <span class="nav-text">Users</span>
                        </a>
                    </li>
                   
                </ul>
            </li>
           

           
            {{-- Original File Management --}}
           

            {{-- Filtered Results --}}
            


            {{-- Admin Specific Sections --}}
            @if(auth()->user()->role === 'admin')
                {{-- <li class="nav-label">Admin</li> --}}
               

                <li>
                    <a class="has-arrow" href="#" aria-expanded="false">
                        <i class="mdi mdi-cogs"></i> <span class="nav-text">Data Cleaning</span>
                    </a>
                    <ul aria-expanded="false">
                       
                        <li>
                            {{-- Points to the list of original uploads --}}
                           <a href="{{ route('uploads.index') }}">
                               <span class="nav-text">Uploads & Scan</span>
                           </a>
                       </li>
                       <li>
                        <a href="{{ route('filtered.index') }}">
                            <span class="nav-text">Filtered Files</span>
                        </a>
                        
                    </li>
                    <li>
                        <a href="{{ route('keywords.index') }}">Keyword Master</a> {{-- Assuming named route 'keywords.index' --}}
                    </li>
                       
                    </ul>
                </li>

               
                {{-- <li>
                    <a href="{{ route('application.settings') }}">
                        <i class="mdi mdi-settings"></i> <span class="nav-text">App Settings</span>
                    </a>
                </li> --}}
          

            @endif
            {{-- End Admin Specific Sections --}}


            {{-- Logout --}}
             {{-- <li class="nav-label">Account</li> --}}
            <li>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="mdi mdi-logout"></i> <span class="nav-text">Logout</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</div>