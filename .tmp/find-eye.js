const out=[];
for(const p of figma.root.children){
  if(!p.findAll) continue;
  for(const n of p.findAll(x=>x.name.toLowerCase().includes('eye'))){out.push({id:n.id,name:n.name,type:n.type,w:n.width,h:n.height}); if(out.length>30) break;}
  if(out.length>30) break;
}
return out;
