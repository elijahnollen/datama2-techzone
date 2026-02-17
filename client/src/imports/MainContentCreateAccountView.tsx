import svgPaths from "./svg-qh90myyie4";

function Header() {
  return (
    <div className="relative shrink-0 w-[625.755px]" data-name="Header">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-center relative w-full">
        <div className="flex flex-col font-['Inter:Bold_Italic',sans-serif] font-bold italic justify-center leading-[0] relative shrink-0 text-[0px] text-black text-center whitespace-nowrap">
          <p className="text-[27.178px]">
            <span className="leading-[33.623px] text-black">{`Create your own `}</span>
            <span className="leading-[33.623px] text-[#06b6d4]">Account</span>
          </p>
        </div>
      </div>
    </div>
  );
}

function Component() {
  return (
    <div className="relative shrink-0 size-[11.208px]" data-name="Component 1">
      <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 11.2075 11.2075">
        <g id="Component 1">
          <path d={svgPaths.p3d2fda00} id="Vector" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="0.933962" />
          <path d={svgPaths.p998ea00} id="Vector_2" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="0.933962" />
        </g>
      </svg>
    </div>
  );
}

function CountryIndicator() {
  return (
    <div className="relative shrink-0 w-[625.755px]" data-name="Country Indicator">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex gap-[7.472px] items-center justify-center relative w-full">
        <Component />
        <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[#a1a1aa] text-[11.208px] text-center tracking-[1.1208px] uppercase whitespace-nowrap">
          <p className="leading-[14.943px]">Philippines</p>
        </div>
      </div>
    </div>
  );
}

function Component2() {
  return (
    <div className="relative shrink-0 size-[18.679px]" data-name="Component 1">
      <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 18.6792 18.6792">
        <g id="Component 1">
          <path d={svgPaths.p3ab4b008} id="Vector" stroke="var(--stroke-0, #A1A1AA)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5566" />
          <path d="M14.7877 9.33962H3.89151" id="Vector_2" stroke="var(--stroke-0, #A1A1AA)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5566" />
        </g>
      </svg>
    </div>
  );
}

function Component1() {
  return (
    <div className="absolute left-[30.82px] top-[30.82px]" data-name="Component 2">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-start relative">
        <Component2 />
      </div>
    </div>
  );
}

function InputWFull() {
  return <div className="absolute bg-[#fafafa] border-[#e4e4e7] border-[0.934px] border-solid h-[46.698px] left-0 right-0 rounded-[11.208px] top-[19.61px]" data-name="input.w-full" />;
}

function Div() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative self-stretch" data-name="div">
      <div className="-translate-y-1/2 absolute flex flex-col font-['Inter:Bold',sans-serif] font-bold h-[14.009px] justify-center leading-[0] left-[3.74px] not-italic text-[#71717a] text-[9.153px] top-[6.54px] tracking-[0.934px] uppercase w-[71.056px]">
        <p className="leading-[14.009px] whitespace-pre-wrap">First name*</p>
      </div>
      <InputWFull />
    </div>
  );
}

function InputWFull1() {
  return <div className="absolute bg-[#fafafa] border-[#e4e4e7] border-[0.934px] border-solid h-[46.698px] left-0 right-0 rounded-[11.208px] top-[19.61px]" data-name="input.w-full" />;
}

function Div1() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative self-stretch" data-name="div">
      <div className="-translate-y-1/2 absolute flex flex-col font-['Inter:Bold',sans-serif] font-bold h-[14.009px] justify-center leading-[0] left-[3.74px] not-italic text-[#71717a] text-[0px] top-[6.54px] tracking-[0.934px] uppercase w-[141.729px]">
        <p className="text-[9.153px] whitespace-pre-wrap">
          <span className="font-['Inter:Bold',sans-serif] font-bold leading-[14.009px] text-[#71717a]">{`Middle Name `}</span>
          <span className="font-['Inter:Regular',sans-serif] font-normal leading-[14.009px] text-[#a1a1aa]">(Optional)</span>
        </p>
      </div>
      <InputWFull1 />
    </div>
  );
}

function InputWFull2() {
  return <div className="absolute bg-[#fafafa] border-[#e4e4e7] border-[0.934px] border-solid h-[46.698px] left-0 right-0 rounded-[11.208px] top-[19.61px]" data-name="input.w-full" />;
}

function Div2() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative self-stretch" data-name="div">
      <div className="-translate-y-1/2 absolute flex flex-col font-['Inter:Bold',sans-serif] font-bold h-[14.009px] justify-center leading-[0] left-[3.74px] not-italic text-[#71717a] text-[9.059px] top-[6.54px] tracking-[0.934px] uppercase w-[67.413px]">
        <p className="leading-[14.009px] whitespace-pre-wrap">Last name*</p>
      </div>
      <InputWFull2 />
    </div>
  );
}

function NameFields() {
  return (
    <div className="content-stretch flex gap-[18.679px] items-start justify-center relative shrink-0 w-full" data-name="Name Fields">
      <Div />
      <Div1 />
      <Div2 />
    </div>
  );
}

function InputWFull3() {
  return (
    <div className="bg-[#fafafa] h-[46px] relative rounded-[11.208px] shrink-0 w-[303px]" data-name="input.w-full">
      <div aria-hidden="true" className="absolute border-[#e4e4e7] border-[0.934px] border-solid inset-0 pointer-events-none rounded-[11.208px]" />
    </div>
  );
}

function PTextZinc() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0 w-[275.019px]" data-name="p.text-zinc-400">
      <div className="flex flex-col font-['Inter:Regular',sans-serif] font-normal justify-center leading-[0] not-italic relative shrink-0 text-[#a1a1aa] text-[8.592px] whitespace-nowrap">
        <p className="leading-[14.009px]">Used for account login and order notifications</p>
      </div>
    </div>
  );
}

function Email() {
  return (
    <div className="content-stretch flex flex-col gap-[6px] items-start relative shrink-0" data-name="Email">
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold h-[14.009px] justify-center leading-[0] not-italic relative shrink-0 text-[#71717a] text-[9.34px] tracking-[0.934px] uppercase w-[89.53px]">
        <p className="leading-[14.009px] whitespace-pre-wrap">Email address</p>
      </div>
      <InputWFull3 />
      <PTextZinc />
    </div>
  );
}

function InputWFull4() {
  return <div className="absolute bg-[#fafafa] border-[#e4e4e7] border-[0.934px] border-solid h-[46.698px] left-0 right-[0.54px] rounded-[11.208px] top-[19.61px]" data-name="input.w-full" />;
}

function Div3() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative self-stretch" data-name="div">
      <div className="-translate-y-1/2 absolute flex flex-col font-['Inter:Bold',sans-serif] font-bold h-[14.009px] justify-center leading-[0] left-[3.74px] not-italic text-[#71717a] text-[9.153px] top-[6.54px] tracking-[0.934px] uppercase w-[152.553px]">
        <p className="leading-[14.009px] whitespace-pre-wrap">Contact number</p>
      </div>
      <InputWFull4 />
    </div>
  );
}

function PasswordFields() {
  return (
    <div className="content-stretch flex gap-[18.679px] items-start justify-center relative shrink-0 w-full" data-name="Password Fields">
      <Email />
      <Div3 />
    </div>
  );
}

function DivPlaceholder() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative" data-name="div#placeholder">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-start overflow-clip pb-[1.868px] pt-[0.934px] relative rounded-[inherit] w-full">
        <div className="flex flex-col font-['Inter:Regular',sans-serif] font-normal justify-center leading-[0] not-italic relative shrink-0 text-[#9ca3af] text-[14.009px] w-full">
          <p className="leading-[normal] whitespace-pre-wrap">House number and street name</p>
        </div>
      </div>
    </div>
  );
}

function InputWFull5() {
  return (
    <div className="absolute bg-[#fafafa] left-0 right-0 rounded-[11.208px] top-[19.61px]" data-name="input.w-full">
      <div className="content-stretch flex items-start justify-center overflow-clip pb-[14.009px] pt-[14.943px] px-[12.142px] relative rounded-[inherit] w-full">
        <DivPlaceholder />
      </div>
      <div aria-hidden="true" className="absolute border-[#e4e4e7] border-[0.934px] border-solid inset-0 pointer-events-none rounded-[11.208px]" />
    </div>
  );
}

function StreetAddress() {
  return (
    <div className="h-[66.311px] relative shrink-0 w-full" data-name="Street Address">
      <div className="-translate-y-1/2 absolute flex flex-col font-['Inter:Bold',sans-serif] font-bold h-[14.009px] justify-center leading-[0] left-[3.74px] not-italic text-[#71717a] text-[9.34px] top-[6.54px] tracking-[0.934px] uppercase w-[102.67px]">
        <p className="leading-[14.009px] whitespace-pre-wrap">Street Address*</p>
      </div>
      <InputWFull5 />
    </div>
  );
}

function Div4() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative" data-name="div">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-start overflow-clip relative rounded-[inherit] w-full">
        <div className="flex flex-col font-['Inter:Regular',sans-serif] font-normal justify-center leading-[0] not-italic relative shrink-0 text-[14.103px] text-black w-full">
          <p className="leading-[22.415px] whitespace-pre-wrap">Select an option...</p>
        </div>
      </div>
    </div>
  );
}

function SelectWFull() {
  return (
    <div className="bg-[#fafafa] relative rounded-[11.208px] shrink-0 w-full" data-name="select.w-full">
      <div aria-hidden="true" className="absolute border-[#e4e4e7] border-[0.934px] border-solid inset-0 pointer-events-none rounded-[11.208px]" />
      <div className="flex flex-row items-center justify-center size-full">
        <div className="content-stretch flex items-center justify-center p-[12.142px] relative w-full">
          <Div4 />
        </div>
      </div>
    </div>
  );
}

function Component3() {
  return (
    <div className="-translate-y-1/2 absolute right-[14.94px] size-[14.943px] top-[calc(50%+0.71px)]" data-name="Component 1">
      <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 14.9434 14.9434">
        <g id="Component 1">
          <path d={svgPaths.p21c90a80} id="Vector" stroke="var(--stroke-0, #A1A1AA)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.24528" />
        </g>
      </svg>
    </div>
  );
}

function DivRelative() {
  return (
    <div className="absolute content-stretch flex flex-col items-start left-0 right-0 top-[19.61px]" data-name="div.relative">
      <SelectWFull />
      <Component3 />
    </div>
  );
}

function CityDropdown() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative self-stretch" data-name="City Dropdown">
      <div className="-translate-y-1/2 absolute flex flex-col font-['Inter:Bold',sans-serif] font-bold h-[14.009px] justify-center leading-[0] left-[3.74px] not-italic text-[#71717a] text-[8.592px] top-[6.54px] tracking-[0.934px] uppercase w-[29.896px]">
        <p className="leading-[14.009px] whitespace-pre-wrap">City*</p>
      </div>
      <DivRelative />
    </div>
  );
}

function Component4() {
  return (
    <div className="-translate-y-1/2 absolute right-[7.47px] size-[14.943px] top-[calc(50%-0.23px)]" data-name="Component 1">
      <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 14.9434 14.9434">
        <g id="Component 1">
          <path d={svgPaths.p21c90a80} id="Vector" stroke="var(--stroke-0, #A1A1AA)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.24528" />
        </g>
      </svg>
    </div>
  );
}

function Div5() {
  return (
    <div className="-translate-y-1/2 absolute content-stretch flex flex-col items-start left-[11.21px] overflow-clip right-[11.21px] top-1/2" data-name="div">
      <Component4 />
      <div className="flex flex-col font-['Inter:Regular',sans-serif] font-normal justify-center leading-[0] min-w-full not-italic relative shrink-0 text-[14.103px] text-black w-[min-content]">
        <p className="leading-[22.415px] whitespace-pre-wrap">Select an option...</p>
      </div>
    </div>
  );
}

function InputWFull6() {
  return (
    <div className="absolute bg-[#fafafa] border-[#e4e4e7] border-[0.934px] border-solid h-[46.698px] left-0 overflow-clip right-0 rounded-[11.208px] top-[19.61px]" data-name="input.w-full">
      <Div5 />
    </div>
  );
}

function Province() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative self-stretch" data-name="Province">
      <div className="-translate-y-1/2 absolute flex flex-col font-['Inter:Bold',sans-serif] font-bold h-[14.009px] justify-center leading-[0] left-[3.74px] not-italic text-[#71717a] text-[9.153px] top-[6.54px] tracking-[0.934px] uppercase w-[60.381px]">
        <p className="leading-[14.009px] whitespace-pre-wrap">Province*</p>
      </div>
      <InputWFull6 />
    </div>
  );
}

function InputWFull7() {
  return <div className="absolute bg-[#fafafa] border-[#e4e4e7] border-[0.934px] border-solid h-[46.698px] left-0 right-0 rounded-[11.208px] top-[19.61px]" data-name="input.w-full" />;
}

function ZipCode() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative self-stretch" data-name="Zip Code">
      <div className="-translate-y-1/2 absolute flex flex-col font-['Inter:Bold',sans-serif] font-bold h-[14.009px] justify-center leading-[0] left-[3.74px] not-italic text-[#71717a] text-[9.153px] top-[6.54px] tracking-[0.934px] uppercase w-[56.281px]">
        <p className="leading-[14.009px] whitespace-pre-wrap">Zip Code*</p>
      </div>
      <InputWFull7 />
    </div>
  );
}

function DivGrid() {
  return (
    <div className="content-stretch flex gap-[18.679px] items-start justify-center relative shrink-0 w-full" data-name="div.grid">
      <CityDropdown />
      <Province />
      <ZipCode />
    </div>
  );
}

function AddressFieldsGroup() {
  return (
    <div className="content-stretch flex flex-col gap-[18.679px] items-start pt-[7.472px] relative shrink-0 w-full" data-name="Address Fields Group">
      <StreetAddress />
      <DivGrid />
    </div>
  );
}

function InputWFull8() {
  return <div className="absolute bg-[#fafafa] border-[#e4e4e7] border-[0.934px] border-solid h-[46.698px] left-0 right-0 rounded-[11.208px] top-[19.61px]" data-name="input.w-full" />;
}

function Div6() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative self-stretch" data-name="div">
      <div className="-translate-y-1/2 absolute flex flex-col font-['Inter:Bold',sans-serif] font-bold h-[14.009px] justify-center leading-[0] left-[3.74px] not-italic text-[#71717a] text-[9.153px] top-[6.54px] tracking-[0.934px] uppercase w-[66.031px]">
        <p className="leading-[14.009px] whitespace-pre-wrap">Password*</p>
      </div>
      <InputWFull8 />
    </div>
  );
}

function InputWFull9() {
  return <div className="absolute bg-[#fafafa] border-[#e4e4e7] border-[0.934px] border-solid h-[46.698px] left-0 right-0 rounded-[11.208px] top-[19.61px]" data-name="input.w-full" />;
}

function Div7() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative self-stretch" data-name="div">
      <div className="-translate-y-1/2 absolute flex flex-col font-['Inter:Bold',sans-serif] font-bold h-[14.009px] justify-center leading-[0] left-[3.74px] not-italic text-[#71717a] text-[9.153px] top-[6.54px] tracking-[0.934px] uppercase w-[152.553px]">
        <p className="leading-[14.009px] whitespace-pre-wrap">Password confirmation*</p>
      </div>
      <InputWFull9 />
    </div>
  );
}

function PasswordFields1() {
  return (
    <div className="content-stretch flex gap-[18.679px] items-start justify-center relative shrink-0 w-full" data-name="Password Fields">
      <Div6 />
      <Div7 />
    </div>
  );
}

function SpanTextXs() {
  return (
    <div className="relative shrink-0" data-name="span.text-xs">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-start relative">
        <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[#52525b] text-[10.834px] whitespace-nowrap">
          <p className="leading-[14.943px]">Subscribe to our newsletter</p>
        </div>
      </div>
    </div>
  );
}

function Component5() {
  return (
    <div className="bg-[#fafafa] content-stretch flex gap-[11.208px] items-center p-[15.877px] relative rounded-[11.208px] shrink-0 w-[625.755px]" data-name="Component 3">
      <div aria-hidden="true" className="absolute border-[#f4f4f5] border-[0.934px] border-solid inset-0 pointer-events-none rounded-[11.208px]" />
      <div className="bg-white relative rounded-[2.335px] shrink-0 size-[14.943px]" data-name="input.w-4">
        <div aria-hidden="true" className="absolute border-[#71717a] border-[0.934px] border-solid inset-0 pointer-events-none rounded-[2.335px]" />
      </div>
      <SpanTextXs />
    </div>
  );
}

function Component6() {
  return (
    <div className="-translate-x-1/2 absolute content-stretch flex items-start justify-center left-[calc(50%+69.96px)] top-[-0.47px]" data-name="Component 4">
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[#0891b2] text-[9.059px] text-center whitespace-nowrap">
        <p className="leading-[15.177px]">terms and conditions</p>
      </div>
    </div>
  );
}

function Agreement() {
  return (
    <div className="h-[30.354px] relative shrink-0 w-full" data-name="Agreement">
      <div className="-translate-x-1/2 -translate-y-1/2 absolute flex flex-col font-['Inter:Regular',sans-serif] font-normal h-[15.877px] justify-center leading-[0] left-[calc(50%-127.39px)] not-italic text-[#a1a1aa] text-[8.779px] text-center top-[7.47px] w-[294.095px]">
        <p className="leading-[15.177px] whitespace-pre-wrap">{`By clicking 'Create account', I hereby agree to and accept the following `}</p>
      </div>
      <Component6 />
      <div className="-translate-x-1/2 -translate-y-1/2 absolute flex flex-col font-['Inter:Regular',sans-serif] font-normal h-[15.877px] justify-center leading-[0] left-[calc(50%+194.24px)] not-italic text-[#a1a1aa] text-[8.592px] text-center top-[7.47px] w-[160.754px]">
        <p className="leading-[15.177px] whitespace-pre-wrap">{` and hereby certify that all of the above`}</p>
      </div>
      <div className="-translate-x-1/2 -translate-y-1/2 absolute flex flex-col font-['Inter:Regular',sans-serif] font-normal h-[15.877px] justify-center leading-[0] left-[calc(50%+0.09px)] not-italic text-[#a1a1aa] text-[8.592px] text-center top-[22.65px] w-[237.768px]">
        <p className="leading-[15.177px] whitespace-pre-wrap">information is true to the best of my knowledge and belief.</p>
      </div>
    </div>
  );
}

function Component7() {
  return (
    <div className="content-stretch flex flex-col items-center justify-center px-[0.934px] py-[15.877px] relative rounded-[11.208px] shrink-0 w-[306.34px]" data-name="Component 5">
      <div aria-hidden="true" className="absolute border-[#e4e4e7] border-[0.934px] border-solid inset-0 pointer-events-none rounded-[11.208px]" />
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[#71717a] text-[11.208px] text-center uppercase whitespace-nowrap">
        <p className="leading-[14.943px]">Cancel</p>
      </div>
    </div>
  );
}

function Component8() {
  return (
    <div className="bg-[#06b6d4] content-stretch flex flex-col items-center justify-center overflow-clip py-[15.877px] relative rounded-[11.208px] shadow-[0px_9.34px_14.009px_-2.802px_rgba(0,0,0,0.1),0px_3.736px_5.604px_-3.736px_rgba(0,0,0,0.1)] shrink-0 w-[304.472px]" data-name="Component 5">
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[11.021px] text-black text-center uppercase whitespace-nowrap">
        <p className="leading-[14.943px]">Create Account</p>
      </div>
    </div>
  );
}

function Buttons() {
  return (
    <div className="content-stretch flex gap-[14.943px] items-start justify-center pt-[7.472px] relative shrink-0 w-full" data-name="Buttons">
      <Component7 />
      <Component8 />
    </div>
  );
}

function Form() {
  return (
    <div className="relative shrink-0 w-[625.755px]" data-name="Form">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col gap-[22.415px] items-start pt-[29.887px] relative w-full">
        <NameFields />
        <PasswordFields />
        <AddressFieldsGroup />
        <PasswordFields1 />
        <Component5 />
        <Agreement />
        <Buttons />
      </div>
    </div>
  );
}

function DivWFull() {
  return (
    <div className="bg-white max-w-[717.2830200195312px] relative rounded-[29.887px] shrink-0 w-[717.283px]" data-name="div.w-full">
      <div className="content-stretch flex flex-col gap-[7.472px] items-start max-w-[inherit] overflow-clip pb-[60.708px] pt-[45.764px] px-[45.764px] relative rounded-[inherit] w-full">
        <Header />
        <CountryIndicator />
        <Component1 />
        <Form />
      </div>
      <div aria-hidden="true" className="absolute border-[#e4e4e7] border-[0.934px] border-solid inset-0 pointer-events-none rounded-[29.887px] shadow-[0px_23.349px_46.698px_-11.208px_rgba(0,0,0,0.25)]" />
    </div>
  );
}

export default function MainContentCreateAccountView() {
  return (
    <div className="content-stretch flex items-center justify-center pb-[74.717px] pt-[119.547px] px-[22.415px] relative size-full" data-name="MAIN CONTENT: CREATE ACCOUNT VIEW">
      <DivWFull />
    </div>
  );
}