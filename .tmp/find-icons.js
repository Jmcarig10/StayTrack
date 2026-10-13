const names=['lucide:arrow-right','lucide:home','lucide:house']; const out=[];
for(const p of figma.root.children){for(const n of p.findAll(x=>names.includes(x.name))){out.push({id:n.id,name:n.name,type:n.type,w:n.width,h:n.height}); if(out.length>60)break;} if(out.length>60)break;}
return out;
