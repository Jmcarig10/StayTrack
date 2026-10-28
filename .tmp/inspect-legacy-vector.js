const root=await figma.getNodeByIdAsync('179:1511');
const names=['Icon / home','Icon / bell','Icon / grid','Icon / maintenance','Icon / wallet','Admin profile icon'];
const out=[];
for(const name of names){const n=root.findAll(x=>x.name===name)[0];out.push(n?{name,id:n.id,type:n.type,w:n.width,h:n.height,desc:n.findAll(x=>x.type==='VECTOR').map(v=>({strokes:v.strokes,fills:v.fills}))}:{name,missing:true});}
return out;
