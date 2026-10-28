const get=async id=>await figma.getNodeByIdAsync(id);
const nav=async(fromId,toId)=>{const from=await get(fromId),to=await get(toId);if(!from||!to)return {from:fromId,to:toId,ok:false,reason:'missing'};try{await from.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:to.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]);return {from:fromId,to:toId,ok:true};}catch(e){return {from:fromId,to:toId,ok:false,reason:String(e).slice(0,320)}}};
const out=[];
// Keep both authentication destinations as top-level page frames so Figma
// accepts navigation in either direction.
const create=await get('309:198');
const page=figma.currentPage;
if(create&&page&&create.parent?.id!==page.id){page.appendChild(create);out.push({move:'309:198',to:'Page 1',ok:true});}
const login=await get('275:913');
if(login&&page&&login.parent?.id!==page.id){page.appendChild(login);out.push({move:'275:913',to:'Page 1',ok:true});}
// Sign-in page registration links and the landing-page account CTAs.
for(const id of ['272:407','272:409','275:971','275:973'])out.push(await nav(id,'309:198'));
for(const id of ['272:340','272:345','275:904','275:909'])out.push(await nav(id,'309:198'));
// Return link on the new registration screen.
for(const id of ['309:256','309:258'])out.push(await nav(id,'275:913'));
return out;
