const result=await (async()=>{
  const screenIds=['284:5601','284:5720','284:5867','284:5983','284:6086','284:6189','284:6327','284:6430','284:6533','284:6636','284:6751','284:6867','358:5091','358:5295','358:5524','358:5710'];
  const ids={dashboard:'284:5601',apartments:'284:5720',apartmentCreate:'284:5867',apartmentShow:'284:5983',apartmentEdit:'284:6086',tenants:'284:6189',tenantCreate:'284:6327',tenantShow:'284:6430',tenantEdit:'284:6533',leases:'284:6636',leaseCreate:'284:6751',leaseShow:'284:6867',maintenance:'358:5091',bills:'358:5295',payments:'358:5524',profile:'358:5710',notifications:'358:5922'};
  const get=async id=>await figma.getNodeByIdAsync(id);
  const roots={}; for(const [k,id] of Object.entries(ids)) roots[k]=await get(id);
  const all=(n,out=[])=>{out.push(n);if(n.children)for(const c of n.children)all(c,out);return out};
  const topOf=n=>{let x=n;while(x?.parent&&x.parent.type!=='PAGE')x=x.parent;return x};
  const screenRootIds=new Set([...screenIds,'358:5922']);
  const screenRootOf=n=>{let x=n;while(x&&x.parent&& !screenRootIds.has(x.id))x=x.parent;return x};
  const isUnder=(n,ancestor)=>{let x=n;while(x){if(x.id===ancestor.id)return true;x=x.parent}return false};
  const cloneReactions=n=>Array.from(n.reactions||[]).map(r=>({trigger:r.trigger,actions:r.actions?Array.from(r.actions).map(a=>({...a})):[]}));
  const result={resized:[],sidebar:0,actions:0,bells:0,notificationClose:0,skipped:0,failed:[],hotspots:0};
  for(const id of screenIds){const n=await get(id);if(!n)continue;if(n.width!==1920||n.height!==1080){n.resize(1920,1080);result.resized.push({id,width:n.width,height:n.height});}}
  const action=(dest,nav='NAVIGATE')=>({type:'NODE',destinationId:dest,navigation:nav,transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}},preserveScrollPosition:false,resetVideoPosition:false});
  const hotspotCache=new Map();
  for(const n of figma.currentPage.findAll(n=>/^Prototype hotspot \/ test dashboard$/.test(n.name||'')))n.remove();
  for(const n of figma.currentPage.findAll(n=>n.name==='Prototype hotspot / View all  →'))n.remove();
  for(const n of figma.currentPage.findAll(n=>/^Prototype hotspot \//.test(n.name||'')).filter(n=>n.parent?.type==='GROUP'))n.remove();
  for(const id of screenIds){const r=await get(id);if(r&&r.parent?.type!=='PAGE'){for(const c of [...r.children].filter(n=>/^Prototype hotspot \//.test(n.name||'')))c.remove();}}
  const sourceFor=async(source,label)=>{
    if(!source)return null;
    const root=screenRootOf(source); if(!root)return null;
    const pageLevel=root.parent?.type!=='PAGE';
    if(!pageLevel&&(root.id===source.id||source.parent?.id===root.id))return source;
    const key=root.id+'|'+source.id; if(hotspotCache.has(key))return hotspotCache.get(key);
    const rb=root.absoluteRenderBounds,sb=source.absoluteRenderBounds; if(!rb||!sb)return source;
    const nm='Prototype hotspot / '+(label||source.name||source.id);
    const exX=pageLevel?sb.x:sb.x-rb.x,exY=pageLevel?sb.y:sb.y-rb.y;
    const existing=(pageLevel?figma.currentPage.children:root.children).find(n=>n.type==='FRAME'&&n.name===nm&&Math.abs(n.x-exX)<1&&Math.abs(n.y-exY)<1);
    if(existing){hotspotCache.set(key,existing);return existing;}
    const h=figma.createFrame(); h.name=nm; h.x=pageLevel?sb.x-rb.x+root.x:sb.x-rb.x; h.y=pageLevel?sb.y-rb.y+root.y:sb.y-rb.y; if(pageLevel){h.x=sb.x;h.y=sb.y;figma.currentPage.appendChild(h);}else root.appendChild(h); h.resize(Math.max(2,sb.width),Math.max(2,sb.height)); h.opacity=0; h.fills=[]; h.strokes=[]; hotspotCache.set(key,h); result.hotspots++; return h;
  };
  const add=async(source,dest,nav='NAVIGATE',label='')=>{
    if(!source||!dest)return false;
    try{
      const sourceRoot=screenRootOf(source); if(sourceRoot?.id===dest.id){result.skipped++;return false;}
      const target=await sourceFor(source,label); if(!target)return false;
      const existing=Array.from(target.reactions||[]);
      if(existing.some(r=>(r.actions||[]).some(a=>a.type==='NODE'&&a.destinationId===dest.id&&a.navigation===nav)))return false;
      const next=cloneReactions(target); next.push({trigger:{type:'ON_CLICK'},actions:[action(dest.id,nav)]}); await target.setReactionsAsync(next); return true;
    }catch(e){result.failed.push({label,source:source.id,error:String(e).slice(0,180)});return false;}
  };
  const sidebarLabel=(root,label)=>{const sb=all(root).find(n=>n.type==='FRAME'&&n.name==='Sidebar');if(!sb)return null;const tx=all(sb).find(n=>n.type==='TEXT'&&n.characters===label);return tx?.parent||tx||null};
  const outsideText=(root,label,which=0)=>{const sb=all(root).find(n=>n.type==='FRAME'&&n.name==='Sidebar');const hits=all(root).filter(n=>n.type==='TEXT'&&n.characters===label&&(!sb||!isUnder(n,sb)));return hits[which]||null};
  const outsideAll=(root,label)=>{const sb=all(root).find(n=>n.type==='FRAME'&&n.name==='Sidebar');return all(root).filter(n=>n.type==='TEXT'&&n.characters===label&&(!sb||!isUnder(n,sb)));};
  const attachText=async(root,label,dest,opts={})=>{const nodes=opts.all?outsideAll(root,label):[outsideText(root,label,opts.which||0)].filter(Boolean);let c=0;for(const tx of nodes){const src=(tx.parent?.name||'').startsWith('Action /')||['Create lease','Add apartment','Add tenant','New request','Manage bills','Save changes','Save apartment','Edit apartment','Cancel','Create active lease'].includes(label)?tx.parent:tx;if(await add(src,dest,opts.nav||'NAVIGATE',label))c++;}return c};
  const navMap={Dashboard:'dashboard',Apartments:'apartments',Tenants:'tenants',Leases:'leases',Maintenance:'maintenance',Bills:'bills',Payments:'payments',Profile:'profile'};
  for(const root of Object.values(roots).filter(Boolean)){
    for(const [label,key] of Object.entries(navMap)){if(await add(sidebarLabel(root,label),roots[key],'NAVIGATE','sidebar:'+label))result.sidebar++;}
    const bell=all(root).find(n=>n.type==='FRAME'&&/bell/i.test(n.name||''));if(bell&&await add(bell,roots.notifications,'OVERLAY','notification bell'))result.bells++;
  }
  result.actions+=await attachText(roots.dashboard,'Create lease',roots.leaseCreate);
  result.actions+=await attachText(roots.dashboard,'Record payment',roots.payments);
  result.actions+=await attachText(roots.dashboard,'Manage bills',roots.bills);
  result.actions+=await attachText(roots.dashboard,'View all  →',roots.payments,{which:0});
  result.actions+=await attachText(roots.dashboard,'View all  →',roots.leases,{which:1});
  result.actions+=await attachText(roots.apartments,'Add apartment',roots.apartmentCreate);
  result.actions+=await attachText(roots.apartmentCreate,'Cancel',roots.apartments);
  result.actions+=await attachText(roots.apartmentCreate,'Save apartment',roots.apartmentShow);
  result.actions+=await attachText(roots.apartmentShow,'Back to apartments',roots.apartments);
  result.actions+=await attachText(roots.apartmentShow,'Edit apartment',roots.apartmentEdit);
  result.actions+=await attachText(roots.apartmentEdit,'Cancel',roots.apartmentShow);
  result.actions+=await attachText(roots.apartmentEdit,'Save changes',roots.apartmentShow);
  result.actions+=await attachText(roots.tenants,'Add tenant',roots.tenantCreate);
  result.actions+=await attachText(roots.tenantCreate,'Cancel',roots.tenants);
  result.actions+=await attachText(roots.tenantCreate,'Save changes',roots.tenantShow);
  result.actions+=await attachText(roots.tenantShow,'Back to tenants',roots.tenants);
  result.actions+=await attachText(roots.tenantShow,'Edit tenant',roots.tenantEdit);
  result.actions+=await attachText(roots.tenantEdit,'Cancel',roots.tenantShow);
  result.actions+=await attachText(roots.tenantEdit,'Save changes',roots.tenantShow);
  result.actions+=await attachText(roots.leases,'Create lease',roots.leaseCreate);
  result.actions+=await attachText(roots.leaseCreate,'Cancel',roots.leases);
  result.actions+=await attachText(roots.leaseCreate,'Create active lease',roots.leaseShow);
  result.actions+=await attachText(roots.leaseShow,'Back to leases',roots.leases);
  result.actions+=await attachText(roots.maintenance,'New request',roots.maintenance);
  result.actions+=await attachText(roots.bills,'Record payment',roots.payments,{all:true});
  result.actions+=await attachText(roots.payments,'Record payment',roots.payments);
  const close=all(roots.notifications).find(n=>n.type==='TEXT'&&n.name==='Close');if(close){try{const target=await sourceFor(close,'notification close');const next=cloneReactions(target);if(!next.some(r=>(r.actions||[]).some(a=>a.type==='CLOSE'))){next.push({trigger:{type:'ON_CLICK'},actions:[{type:'CLOSE'}]});await target.setReactionsAsync(next);result.notificationClose++;}}catch(e){result.failed.push({label:'notification close',source:close.id,error:String(e).slice(0,180)});}}
  return result;
})(); return result;
