<aside class="w-64 border-r border-slate-200 dark:border-border-dark flex flex-col fixed h-full bg-white dark:bg-background-dark z-50">
    <!-- 3D Logo -->
    <div class="flex justify-center pt-4 pb-2">
        <div id="sb-logo3d" style="width:160px;height:90px;position:relative;cursor:pointer" onclick="window.location.href='../home/'">
            <div style="position:absolute;inset:0;background:linear-gradient(to bottom,transparent 50%,rgba(124,58,237,0.025) 51%,transparent 51%);background-size:100% 4px;pointer-events:none;z-index:2;border-radius:8px"></div>
        </div>
    </div>
    <script>
    (function(){
      if(document.getElementById('sb-logo3d-init'))return;
      var s=document.createElement('script');s.id='sb-logo3d-init';
      s.src='https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js';
      s.onload=function(){
        var el=document.getElementById('sb-logo3d');if(!el)return;
        var W=160,H=90,scene=new THREE.Scene();
        var cam=new THREE.PerspectiveCamera(50,W/H,0.1,100);cam.position.z=4;
        var ren=new THREE.WebGLRenderer({antialias:true,alpha:true});
        ren.setSize(W,H);ren.setPixelRatio(Math.min(devicePixelRatio,2));
        el.appendChild(ren.domElement);
        var clk=new THREE.Clock(),mesh=null,mx=0,my=0;
        scene.add(new THREE.AmbientLight(0xffffff,0.9));
        var pl=new THREE.PointLight(0xa855f7,2,20);pl.position.set(3,3,3);scene.add(pl);
        new THREE.TextureLoader().load('https://i.imgur.com/C5tqLgx.png',function(tex){
          tex.minFilter=THREE.LinearFilter;
          var geo=new THREE.PlaneGeometry(3,2);
          var mat=new THREE.MeshBasicMaterial({map:tex,transparent:true,side:THREE.DoubleSide});
          mesh=new THREE.Mesh(geo,mat);scene.add(mesh);
        });
        document.addEventListener('mousemove',function(e){mx=(e.clientX/innerWidth-0.5)*2;my=(e.clientY/innerHeight-0.5)*2;});
        (function anim(){
          requestAnimationFrame(anim);
          var t=clk.getElapsedTime();
          if(mesh){mesh.rotation.y+=0.012;mesh.rotation.x=THREE.MathUtils.lerp(mesh.rotation.x,my*0.3,0.05);mesh.position.y=Math.sin(t)*0.12;}
          ren.render(scene,cam);
        })();
      };
      document.head.appendChild(s);
    })();
    </script>
    <nav class="flex-1 px-4 space-y-6">
        <div>
            <p class="px-4 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-4">Principal</p>
            <ul class="space-y-1">
                <li>
                    <a class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-medium transition-all" href="../home/">
                        <span class="material-icons-round text-[20px]">dashboard</span>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-surface-dark hover:text-primary dark:hover:text-primary transition-all group" href="#">
                        <span class="material-icons-round text-[20px] group-hover:text-primary">assessment</span>
                        Relatórios
                        <span class="material-icons-round ml-auto text-sm opacity-50">expand_more</span>
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-surface-dark hover:text-primary dark:hover:text-primary transition-all group" href="../financeiro/">
                        <span class="material-icons-round text-[20px] group-hover:text-primary">shopping_cart</span>
                        Minhas Vendas
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-surface-dark hover:text-primary dark:hover:text-primary transition-all group" href="../checkout-build/">
                        <span class="material-icons-round text-[20px] group-hover:text-primary">inventory_2</span>
                        Produtos
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-surface-dark hover:text-primary dark:hover:text-primary transition-all group" href="../payment-links/">
                        <span class="material-icons-round text-[20px] group-hover:text-primary">link</span>
                        Links de Pagamento
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-surface-dark hover:text-primary dark:hover:text-primary transition-all group" href="../planos/">
                        <span class="material-icons-round text-[20px] group-hover:text-primary">stars</span>
                        Meu Plano
                    </a>
                </li>
            </ul>
        </div>
        <div>
            <ul class="space-y-1">

                <li>
                    <a class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-surface-dark hover:text-primary dark:hover:text-primary transition-all group" href="../provedores/">
                        <span class="material-icons-round text-[20px] group-hover:text-primary">hub</span>
                        Provedores
                    </a>
                </li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-surface-dark hover:text-primary dark:hover:text-primary transition-all group" href="../dominios/">
                        <span class="material-icons-round text-[20px] group-hover:text-primary">language</span>
                        Domínios
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="p-4 border-t border-slate-200 dark:border-border-dark">
        <button onclick="window.location.href='../logout.php'" class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-red-500/10 hover:text-red-500 transition-all">
            <span class="material-icons-round text-[20px]">logout</span>
            Sair
        </button>
    </div>
</aside>