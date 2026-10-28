const n=await figma.getNodeByIdAsync('283:2454');
const seg=n.getStyledTextSegments(['fontName'])[0];
if(seg) await figma.loadFontAsync(seg.fontName);
n.characters='New to StayTrack?  Create an account';
return {updated:n.id};
