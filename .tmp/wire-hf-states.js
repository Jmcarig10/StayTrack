const get=async(id)=>await figma.getNodeByIdAsync(id);
const link=async(f,t)=>{if(!f||!t)return false;await f.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:t.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]);return true;};
const textParent=(root,value)=>{const n=root.findAll((x)=>x.type==='TEXT'&&x.characters===value)[0];return n?n.parent:null;};
const indexIds=['277:1161','278:1636','278:1985','278:2323','278:2661','278:2999','278:3503'];
const modal=await get('282:1698'), toast=await get('282:1714'), apartmentCreate=await get('277:1304'), index=await get('277:1161');
let count=0;
for(const id of indexIds){const root=await get(id);const icons=root.findAll((n)=>n.name==='lucide:ellipsis');if(icons[0]&&await link(icons[0],modal))count++;}
if(await link(textParent(modal,'Cancel'),index))count++;
if(await link(textParent(modal,'Yes, delete'),toast))count++;
const validation=await get('282:1723');if(await link(textParent(validation,'Cancel'),index))count++;if(await link(textParent(validation,'Save apartment'),toast))count++;
const empty=await get('282:1737');if(await link(textParent(empty,'Add apartment'),apartmentCreate))count++;
return {count};
