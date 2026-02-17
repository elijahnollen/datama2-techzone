import svgPaths from "./svg-4q68ew1kfa";

function Component2() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0" data-name="Component 12">
      <div className="flex flex-col font-['Inter:Bold_Italic',sans-serif] font-bold italic justify-center leading-[0] relative shrink-0 text-[#18181b] text-[26.688px] tracking-[-1.371px] whitespace-nowrap">
        <p className="leading-[32.903px]">TECHZONE</p>
      </div>
    </div>
  );
}

function Component() {
  return (
    <div className="relative shrink-0 size-[18.279px]" data-name="Component 5">
      <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18.2795 18.2795">
        <g id="Component 5">
          <path d={svgPaths.p3cc2580} id="Vector" stroke="var(--stroke-0, #71717A)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.52329" />
        </g>
      </svg>
    </div>
  );
}

function Component1() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0" data-name="Component 10">
      <Component />
    </div>
  );
}

function Component3() {
  return (
    <div className="bg-black content-stretch flex flex-col items-center justify-center overflow-clip px-[29.247px] py-[9.14px] relative rounded-[9138.829px] shadow-[0px_9.14px_13.71px_-2.742px_rgba(0,0,0,0.1),0px_3.656px_5.484px_-3.656px_rgba(0,0,0,0.1)] shrink-0" data-name="Component 13">
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[10.968px] text-center text-white tracking-[0.2742px] uppercase whitespace-nowrap">
        <p className="leading-[14.624px]">Log In</p>
      </div>
    </div>
  );
}

function DivFlex() {
  return (
    <div className="content-stretch flex gap-[21.935px] items-center relative shrink-0" data-name="div.flex">
      <Component1 />
      <Component3 />
    </div>
  );
}

function DivContainer() {
  return (
    <div className="max-w-[1403.864501953125px] relative shrink-0 w-full" data-name="div.container">
      <div className="flex flex-row items-center max-w-[inherit] size-full">
        <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex gap-[1083.736px] items-center max-w-[inherit] px-[21.935px] relative w-full">
          <Component2 />
          <DivFlex />
        </div>
      </div>
    </div>
  );
}

export default function NavbarWhiteBackground() {
  return (
    <div className="backdrop-blur-[10.968px] bg-[rgba(255,255,255,0.95)] content-stretch flex flex-col items-start pb-[15.538px] pt-[14.624px] px-[175.483px] relative size-full" data-name="Navbar (White Background">
      <div aria-hidden="true" className="absolute border-[#f4f4f5] border-b-[0.914px] border-solid inset-0 pointer-events-none shadow-[0px_0.914px_1.828px_0px_rgba(0,0,0,0.05)]" />
      <DivContainer />
    </div>
  );
}