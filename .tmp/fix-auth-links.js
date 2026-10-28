const get=async id=>await figma.getNodeByIdAsync(id);
const link=async(fromId,toId)=>{const from=await get(fromId),to=await get(toId);if(!from||!to)return false;await from.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:to.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]);return true;};
const pairs=[
  ['278:3929','278:3988'], // generic login forgot password
  ['278:4009','278:4013'], // email reset submit
  ['278:4000','278:3890'], // forgot back
  ['278:4045','278:3890'], // reset submit
  ['278:4068','278:3890'], // verify logout
  ['278:4092','277:1041'], // confirm password -> admin workspace
  ['275:897','275:799'],   // duplicate landing sign in
  ['275:909','278:3944'],  // duplicate landing create account
  ['275:912','278:3944'],
  ['275:968','277:1041'],  // duplicate login sign in
  ['275:957','278:3988'],  // duplicate login forgot
  ['275:973','278:3944']
];
let count=0;for(const [a,b] of pairs)if(await link(a,b))count++;return {count};
