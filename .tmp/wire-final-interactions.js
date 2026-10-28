const pairs=[
  ['283:1983','282:1262'],
  ['283:1992','282:1371'],
  ['283:2001','282:1486'],
  ['283:2040','282:1486'],
  ['283:2057','282:1596'],
  ['283:2468','277:1041'],
  ['283:2476','277:1161'],
  ['283:2483','278:1636'],
  ['283:2489','278:1985'],
  ['283:2495','278:2323'],
  ['283:2499','278:2661'],
  ['283:2505','278:2999'],
  ['283:2511','278:3337']
];
const out=[];
for(const [a,b] of pairs){
  try{
    const f=await figma.getNodeByIdAsync(a), t=await figma.getNodeByIdAsync(b);
    await f.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:t.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]);
    out.push({a,b,ok:true});
  }catch(e){out.push({a,b,ok:false,error:String(e)});}
}
return out;
