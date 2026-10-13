const root=await figma.getNodeByIdAsync('284:5720');
const out=[];
const walk=(n,d=0)=>{if(d>5)return;out.push({d,id:n.id,name:n.name,type:n.type,text:n.type==='TEXT'?n.characters:null,x:n.x,y:n.y,w:n.width,h:n.height,children:n.children?.length||0});if(n.children)for(const c of n.children)walk(c,d+1)};
walk(root);return out;
