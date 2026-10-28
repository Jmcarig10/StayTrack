const root = await figma.getNodeByIdAsync('283:1899');
const out=[];
const walk=(n,d=0)=>{out.push({id:n.id,name:n.name,type:n.type,x:Math.round(n.x),y:Math.round(n.y),w:Math.round(n.width),h:Math.round(n.height),chars:n.type==='TEXT'?n.characters.slice(0,80):undefined});if(d<3&&n.children)for(const c of n.children)walk(c,d+1);};
walk(root); return out;
