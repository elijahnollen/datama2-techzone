import svgPaths from "./svg-4wtjts7dff";

function Component() {
  return (
    <div className="relative shrink-0 size-[16px]" data-name="Component 1">
      <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 16 16">
        <g id="Component 1">
          <path d={svgPaths.p203476e0} id="Vector" stroke="var(--stroke-0, #71717A)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d="M12.6667 8H3.33333" id="Vector_2" stroke="var(--stroke-0, #71717A)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
        </g>
      </svg>
    </div>
  );
}

function Component1() {
  return (
    <div className="content-stretch flex gap-[8px] items-center relative shrink-0" data-name="Component 2">
      <Component />
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[#71717a] text-[12px] tracking-[1.2px] uppercase whitespace-nowrap">
        <p className="leading-[16px]">Back to Order</p>
      </div>
    </div>
  );
}

function BackButton() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0 w-full" data-name="Back Button">
      <Component1 />
    </div>
  );
}

function H1Text3Xl() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0 w-full" data-name="h1.text-3xl">
      <div className="flex flex-col font-['Inter:Bold_Italic',sans-serif] font-bold italic justify-center leading-[0] relative shrink-0 text-[0px] text-black tracking-[-1.5px] whitespace-nowrap">
        <p className="text-[30px]">
          <span className="leading-[36px] text-black">{`RETURN `}</span>
          <span className="leading-[36px] text-[#06b6d4]">REQUEST</span>
        </p>
      </div>
    </div>
  );
}

function PTextZinc1() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0 w-full" data-name="p.text-zinc-500">
      <div className="flex flex-col font-['Inter:Regular',sans-serif] font-normal justify-center leading-[0] not-italic relative shrink-0 text-[#71717a] text-[12.9px] whitespace-nowrap">
        <p className="leading-[20px]">Submit a request to return items from Order #TZ-9928</p>
      </div>
    </div>
  );
}

function Div() {
  return (
    <div className="content-stretch flex flex-col gap-[8px] items-start relative shrink-0" data-name="div">
      <H1Text3Xl />
      <PTextZinc1 />
    </div>
  );
}

function DivTextXs() {
  return (
    <div className="content-stretch flex flex-col items-end relative shrink-0 w-full" data-name="div.text-xs">
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[#a1a1aa] text-[12px] text-right tracking-[1.2px] uppercase whitespace-nowrap">
        <p className="leading-[16px]">Order Date</p>
      </div>
    </div>
  );
}

function DivFontBold() {
  return (
    <div className="content-stretch flex flex-col items-end relative shrink-0 w-full" data-name="div.font-bold">
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[14.6px] text-black text-right whitespace-nowrap">
        <p className="leading-[24px]">Feb 08, 2026</p>
      </div>
    </div>
  );
}

function DivTextRight() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0" data-name="div.text-right">
      <DivTextXs />
      <DivFontBold />
    </div>
  );
}

function DivFlex() {
  return (
    <div className="content-stretch flex items-center justify-between relative shrink-0 w-full" data-name="div.flex">
      <Div />
      <DivTextRight />
    </div>
  );
}

function Div2() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative" data-name="div">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-start overflow-auto relative w-full">
        <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[#52525b] text-[13.7px] w-full">
          <p className="leading-[20px] whitespace-pre-wrap">Pilot Zero</p>
        </div>
      </div>
    </div>
  );
}

function InputWFull() {
  return (
    <div className="absolute bg-[#fafafa] left-0 right-0 rounded-[12px] top-[21px]" data-name="input.w-full">
      <div className="content-stretch flex items-start justify-center overflow-clip p-[13px] relative rounded-[inherit] w-full">
        <Div2 />
      </div>
      <div aria-hidden="true" className="absolute border border-[#e4e4e7] border-solid inset-0 pointer-events-none rounded-[12px]" />
    </div>
  );
}

function Div1() {
  return (
    <div className="flex-[1_0_0] h-[67px] min-h-px min-w-px relative" data-name="div">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid relative size-full">
        <div className="-translate-y-1/2 absolute flex flex-col font-['Inter:Bold',sans-serif] font-bold h-[15px] justify-center leading-[0] left-[4px] not-italic text-[#71717a] text-[9.8px] top-[7px] uppercase w-[89.29px]">
          <p className="leading-[15px] whitespace-pre-wrap">Customer Name</p>
        </div>
        <InputWFull />
      </div>
    </div>
  );
}

function Div4() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative" data-name="div">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-start overflow-auto relative w-full">
        <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[#52525b] text-[12px] w-full">
          <p className="leading-[20px] whitespace-pre-wrap">#TZ-9928</p>
        </div>
      </div>
    </div>
  );
}

function InputWFull1() {
  return (
    <div className="absolute bg-[#fafafa] left-0 right-0 rounded-[12px] top-[21px]" data-name="input.w-full">
      <div className="content-stretch flex items-start justify-center overflow-clip p-[13px] relative rounded-[inherit] w-full">
        <Div4 />
      </div>
      <div aria-hidden="true" className="absolute border border-[#e4e4e7] border-solid inset-0 pointer-events-none rounded-[12px]" />
    </div>
  );
}

function Div3() {
  return (
    <div className="flex-[1_0_0] h-[67px] min-h-px min-w-px relative" data-name="div">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid relative size-full">
        <div className="-translate-y-1/2 absolute flex flex-col font-['Inter:Bold',sans-serif] font-bold h-[15px] justify-center leading-[0] left-[4px] not-italic text-[#71717a] text-[10px] top-[7px] uppercase w-[49.08px]">
          <p className="leading-[15px] whitespace-pre-wrap">Order ID</p>
        </div>
        <InputWFull1 />
      </div>
    </div>
  );
}

function Component1ContactOrderInfoReadOnly() {
  return (
    <div className="content-stretch flex gap-[24px] items-start justify-center pb-[33px] relative shrink-0 w-full" data-name="1. Contact & Order Info (Read Only">
      <div aria-hidden="true" className="absolute border-[#f4f4f5] border-b border-solid inset-0 pointer-events-none" />
      <Div1 />
      <Div3 />
    </div>
  );
}

function Component2() {
  return (
    <div className="relative shrink-0 size-[16px]" data-name="Component 1">
      <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 16 16">
        <g id="Component 1">
          <path d={svgPaths.p34deff00} id="Vector" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d={svgPaths.p2a109880} id="Vector_2" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d={svgPaths.p310f7b00} id="Vector_3" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d={svgPaths.p3e9dc000} id="Vector_4" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d="M6 2H6.66667" id="Vector_5" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d="M6 14H6.66667" id="Vector_6" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d="M9.33333 2H10" id="Vector_7" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d="M9.33333 14H10" id="Vector_8" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d="M2 6V6.66667" id="Vector_9" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d="M14 6V6.66667" id="Vector_10" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d="M2 9.33333V10" id="Vector_11" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d="M14 9.33333V10" id="Vector_12" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
        </g>
      </svg>
    </div>
  );
}

function H3TextSm() {
  return (
    <div className="content-stretch flex gap-[8px] items-center relative shrink-0 w-full" data-name="h3.text-sm">
      <Component2 />
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[14px] text-black tracking-[1.4px] uppercase whitespace-nowrap">
        <p className="leading-[20px]">Select Item to Return</p>
      </div>
    </div>
  );
}

function Photo15914883989992D1072A() {
  return <div className="flex-[1_0_0] h-[62px] min-h-px min-w-px" data-name="photo-1591488398-9992d1072a28" />;
}

function DivW1() {
  return (
    <div className="bg-white relative rounded-[8px] shrink-0 size-[80px]" data-name="div.w-20">
      <div aria-hidden="true" className="absolute border border-[#e4e4e7] border-solid inset-0 pointer-events-none rounded-[8px]" />
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex items-center justify-center p-[9px] relative size-full">
        <Photo15914883989992D1072A />
      </div>
    </div>
  );
}

function H4FontBold() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0 w-full" data-name="h4.font-bold">
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[13.6px] text-black w-full">
        <p className="leading-[20px] whitespace-pre-wrap">Module-X Alpha Graphics Unit</p>
      </div>
    </div>
  );
}

function PTextXs() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0 w-full" data-name="p.text-xs">
      <div className="flex flex-col font-['Inter:Regular',sans-serif] font-normal justify-center leading-[0] not-italic relative shrink-0 text-[#71717a] text-[11.6px] w-full">
        <p className="leading-[16px] whitespace-pre-wrap">Variant: 24GB VRAM</p>
      </div>
    </div>
  );
}

function DivTextSm() {
  return (
    <div className="content-stretch flex flex-col items-start pt-[4px] relative shrink-0 w-full" data-name="div.text-sm">
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[#0891b2] text-[11.9px] w-full">
        <p className="leading-[20px] whitespace-pre-wrap">₱899.00</p>
      </div>
    </div>
  );
}

function DivFlexGrow() {
  return (
    <div className="flex-[1_0_0] h-[80px] min-h-px min-w-px relative" data-name="div.flex-grow">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col gap-[4px] items-start pr-[32px] relative size-full">
        <H4FontBold />
        <PTextXs />
        <DivTextSm />
      </div>
    </div>
  );
}

function DivAbsolute() {
  return (
    <div className="absolute right-[17px] top-[17px]" data-name="div.absolute">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-start relative">
        <div className="bg-[#06b6d4] rounded-[2.5px] shrink-0 size-[20px]" data-name="input.w-5" />
      </div>
    </div>
  );
}

function ItemCard() {
  return (
    <div className="bg-[rgba(236,254,255,0.05)] relative rounded-[12px] shrink-0 w-full" data-name="Item Card">
      <div className="overflow-clip rounded-[inherit] size-full">
        <div className="content-stretch flex gap-[24px] items-start p-[17px] relative w-full">
          <DivW1 />
          <DivFlexGrow />
          <DivAbsolute />
        </div>
      </div>
      <div aria-hidden="true" className="absolute border border-[#06b6d4] border-solid inset-0 pointer-events-none rounded-[12px]" />
    </div>
  );
}

function Component2SelectItems() {
  return (
    <div className="content-stretch flex flex-col gap-[24px] items-start relative shrink-0 w-full" data-name="2. Select Items">
      <H3TextSm />
      <ItemCard />
    </div>
  );
}

function Component3() {
  return (
    <div className="relative shrink-0 size-[16px]" data-name="Component 1">
      <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 16 16">
        <g id="Component 1">
          <path d={svgPaths.p28b0a6c0} id="Vector" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d={svgPaths.p2f10900} id="Vector_2" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d="M6.66667 6H5.33333" id="Vector_3" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d="M10.6667 8.66667H5.33333" id="Vector_4" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d="M10.6667 11.3333H5.33333" id="Vector_5" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
        </g>
      </svg>
    </div>
  );
}

function H3TextSm1() {
  return (
    <div className="content-stretch flex gap-[8px] items-center relative shrink-0 w-full" data-name="h3.text-sm">
      <Component3 />
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[14px] text-black tracking-[1.4px] uppercase whitespace-nowrap">
        <p className="leading-[20px]">Reason for Return</p>
      </div>
    </div>
  );
}

function Div6() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative" data-name="div">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-start overflow-clip relative rounded-[inherit] w-full">
        <div className="flex flex-col font-['Inter:Regular',sans-serif] font-normal justify-center leading-[0] not-italic relative shrink-0 text-[13.3px] text-black w-full">
          <p className="leading-[20px] whitespace-pre-wrap">Select a reason...</p>
        </div>
      </div>
    </div>
  );
}

function SelectWFull() {
  return (
    <div className="bg-white relative rounded-[12px] shrink-0 w-full" data-name="select.w-full">
      <div aria-hidden="true" className="absolute border border-[#e4e4e7] border-solid inset-0 pointer-events-none rounded-[12px]" />
      <div className="flex flex-row items-center justify-center size-full">
        <div className="content-stretch flex items-center justify-center p-[13px] relative w-full">
          <Div6 />
        </div>
      </div>
    </div>
  );
}

function Component4() {
  return (
    <div className="-translate-y-1/2 absolute right-[16px] size-[16px] top-1/2" data-name="Component 1">
      <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 16 16">
        <g id="Component 1">
          <path d="M4 6L8 10L12 6" id="Vector" stroke="var(--stroke-0, #A1A1AA)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
        </g>
      </svg>
    </div>
  );
}

function DivRelative() {
  return (
    <div className="absolute content-stretch flex flex-col items-start left-0 right-0 top-[21px]" data-name="div.relative">
      <SelectWFull />
      <Component4 />
    </div>
  );
}

function Div5() {
  return (
    <div className="h-[67px] relative shrink-0 w-full" data-name="div">
      <div className="-translate-y-1/2 absolute flex flex-col font-['Inter:Bold',sans-serif] font-bold h-[15px] justify-center leading-[0] left-[4px] not-italic text-[#71717a] text-[9.8px] top-[7px] uppercase w-[46.65px]">
        <p className="leading-[15px] whitespace-pre-wrap">Reason*</p>
      </div>
      <DivRelative />
    </div>
  );
}

function DivPlaceholder() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative" data-name="div#placeholder">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-start relative w-full">
        <div className="flex flex-col font-['Inter:Regular',sans-serif] font-normal justify-center leading-[0] not-italic relative shrink-0 text-[#9ca3af] text-[13.1px] w-full">
          <p className="leading-[20px] whitespace-pre-wrap">Please provide more details about the issue...</p>
        </div>
      </div>
    </div>
  );
}

function TextareaWFull() {
  return (
    <div className="absolute bg-white left-0 min-h-[120px] right-0 rounded-[12px] top-[21px]" data-name="textarea.w-full">
      <div className="content-stretch flex items-start justify-center min-h-[inherit] overflow-auto pb-[87px] pt-[13px] px-[13px] relative w-full">
        <DivPlaceholder />
      </div>
      <div aria-hidden="true" className="absolute border border-[#e4e4e7] border-solid inset-0 pointer-events-none rounded-[12px]" />
    </div>
  );
}

function Div7() {
  return (
    <div className="h-[141px] relative shrink-0 w-full" data-name="div">
      <div className="-translate-y-1/2 absolute flex flex-col font-['Inter:Bold',sans-serif] font-bold h-[15px] justify-center leading-[0] left-[4px] not-italic text-[#71717a] text-[9.8px] top-[7px] uppercase w-[72.39px]">
        <p className="leading-[15px] whitespace-pre-wrap">Description*</p>
      </div>
      <TextareaWFull />
    </div>
  );
}

function Component6() {
  return (
    <div className="relative shrink-0 size-[24px]" data-name="Component 1">
      <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 24 24">
        <g id="Component 1">
          <path d="M12 13V21" id="Vector" stroke="var(--stroke-0, #A1A1AA)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" />
          <path d={svgPaths.p34574300} id="Vector_2" stroke="var(--stroke-0, #A1A1AA)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" />
          <path d="M8 17L12 13L16 17" id="Vector_3" stroke="var(--stroke-0, #A1A1AA)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" />
        </g>
      </svg>
    </div>
  );
}

function DivW() {
  return (
    <div className="-translate-x-1/2 absolute bg-[#f4f4f5] content-stretch flex items-center justify-center left-1/2 rounded-[9999px] size-[48px] top-[32px]" data-name="div.w-12">
      <Component6 />
    </div>
  );
}

function PTextXs1() {
  return (
    <div className="absolute content-stretch flex flex-col items-center left-[32px] right-[32px] top-[96px]" data-name="p.text-xs">
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[#52525b] text-[11.8px] text-center whitespace-nowrap">
        <p className="leading-[16px]">Click to upload or drag and drop</p>
      </div>
    </div>
  );
}

function PTextZinc() {
  return (
    <div className="absolute content-stretch flex flex-col items-center left-[32px] right-[32px] top-[116px]" data-name="p.text-zinc-400">
      <div className="flex flex-col font-['Inter:Regular',sans-serif] font-normal justify-center leading-[0] not-italic relative shrink-0 text-[#a1a1aa] text-[9.8px] text-center whitespace-nowrap">
        <p className="leading-[15px]">SVG, PNG, JPG or MP4 (max. 10MB)</p>
      </div>
    </div>
  );
}

function Component5() {
  return (
    <div className="absolute border-2 border-[#d4d4d8] border-dashed h-[167px] left-0 right-0 rounded-[12px] top-[21px]" data-name="Component 3">
      <DivW />
      <PTextXs1 />
      <PTextZinc />
    </div>
  );
}

function FileUpload() {
  return (
    <div className="h-[188px] relative shrink-0 w-full" data-name="File Upload">
      <div className="-translate-y-1/2 absolute flex flex-col font-['Inter:Bold',sans-serif] font-bold h-[15px] justify-center leading-[0] left-[4px] not-italic text-[#71717a] text-[9.8px] top-[7px] uppercase w-[142.84px]">
        <p className="leading-[15px] whitespace-pre-wrap">Evidence (Photos/Videos)</p>
      </div>
      <Component5 />
    </div>
  );
}

function DivSpaceY() {
  return (
    <div className="content-stretch flex flex-col gap-[24px] items-start relative shrink-0 w-full" data-name="div.space-y-6">
      <Div5 />
      <Div7 />
      <FileUpload />
    </div>
  );
}

function Component3ReturnDetails() {
  return (
    <div className="content-stretch flex flex-col gap-[24px] items-start relative shrink-0 w-full" data-name="3. Return Details">
      <H3TextSm1 />
      <DivSpaceY />
    </div>
  );
}

function Component7() {
  return (
    <div className="relative shrink-0 size-[16px]" data-name="Component 1">
      <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 16 16">
        <g id="Component 1">
          <path d={svgPaths.p264a0480} id="Vector" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d="M10 12H6" id="Vector_2" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d={svgPaths.p37bb0d00} id="Vector_3" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d={svgPaths.p1c171d80} id="Vector_4" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d={svgPaths.p48c6d00} id="Vector_5" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
        </g>
      </svg>
    </div>
  );
}

function H3TextSm2() {
  return (
    <div className="content-stretch flex gap-[8px] items-center relative shrink-0 w-full" data-name="h3.text-sm">
      <Component7 />
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[14px] text-black tracking-[1.4px] uppercase whitespace-nowrap">
        <p className="leading-[20px]">Return Shipment Method</p>
      </div>
    </div>
  );
}

function InputW() {
  return (
    <div className="bg-white relative rounded-[50px] shrink-0 size-[16px]" data-name="input.w-4">
      <div aria-hidden="true" className="absolute border border-[#06b6d4] border-solid inset-0 pointer-events-none rounded-[50px]" />
      <div className="absolute bg-[#06b6d4] left-[3.2px] rounded-[50px] size-[9.6px] top-[3.2px]" data-name="input.w-4:checked" />
    </div>
  );
}

function InputW4Margin() {
  return (
    <div className="h-[20px] relative shrink-0 w-[16px]" data-name="input.w-4:margin">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-start pt-[4px] relative size-full">
        <InputW />
      </div>
    </div>
  );
}

function SpanBlock() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0 w-full" data-name="span.block">
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[13.7px] text-black whitespace-nowrap">
        <p className="leading-[20px]">Courier Pickup</p>
      </div>
    </div>
  );
}

function SpanBlock1() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0 w-full" data-name="span.block">
      <div className="flex flex-col font-['Inter:Regular',sans-serif] font-normal justify-center leading-[0] not-italic relative shrink-0 text-[#71717a] text-[9.2px] whitespace-nowrap">
        <p className="leading-[15px]">We will arrange a courier to pick up the item from your delivery address.</p>
      </div>
    </div>
  );
}

function Div8() {
  return (
    <div className="relative shrink-0" data-name="div">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col gap-[4px] items-start relative">
        <SpanBlock />
        <SpanBlock1 />
      </div>
    </div>
  );
}

function LabelFlex() {
  return (
    <div className="bg-[rgba(236,254,255,0.1)] flex-[1_0_0] min-h-px min-w-px relative rounded-[12px] self-stretch" data-name="label.flex">
      <div aria-hidden="true" className="absolute border border-[#06b6d4] border-solid inset-0 pointer-events-none rounded-[12px]" />
      <div className="content-stretch flex gap-[12px] items-start p-[17px] relative size-full">
        <InputW4Margin />
        <Div8 />
      </div>
    </div>
  );
}

function InputW4Margin1() {
  return (
    <div className="h-[20px] relative shrink-0 w-[16px]" data-name="input.w-4:margin">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-start pt-[4px] relative size-full">
        <div className="bg-white relative rounded-[50px] shrink-0 size-[16px]" data-name="input.w-4">
          <div aria-hidden="true" className="absolute border border-[#71717a] border-solid inset-0 pointer-events-none rounded-[50px]" />
        </div>
      </div>
    </div>
  );
}

function SpanBlock2() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0 w-full" data-name="span.block">
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[13.3px] text-black whitespace-nowrap">
        <p className="leading-[20px]">Drop Off (Self-Return)</p>
      </div>
    </div>
  );
}

function SpanBlock3() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0 w-full" data-name="span.block">
      <div className="flex flex-col font-['Inter:Regular',sans-serif] font-normal justify-center leading-[0] not-italic relative shrink-0 text-[#71717a] text-[9.2px] whitespace-nowrap">
        <p className="leading-[15px]">{`Return the item to the nearest branch or LBC/J&T partner outlet yourself.`}</p>
      </div>
    </div>
  );
}

function Div9() {
  return (
    <div className="relative shrink-0" data-name="div">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col gap-[4px] items-start relative">
        <SpanBlock2 />
        <SpanBlock3 />
      </div>
    </div>
  );
}

function LabelFlex1() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative rounded-[12px] self-stretch" data-name="label.flex">
      <div aria-hidden="true" className="absolute border border-[#e4e4e7] border-solid inset-0 pointer-events-none rounded-[12px]" />
      <div className="content-stretch flex gap-[12px] items-start p-[17px] relative size-full">
        <InputW4Margin1 />
        <Div9 />
      </div>
    </div>
  );
}

function DivGrid() {
  return (
    <div className="content-stretch flex gap-[16px] items-start justify-center relative shrink-0 w-full" data-name="div.grid">
      <LabelFlex />
      <LabelFlex1 />
    </div>
  );
}

function Component4ReturnMethodNewSection() {
  return (
    <div className="content-stretch flex flex-col gap-[24px] items-start relative shrink-0 w-full" data-name="4. Return Method (NEW SECTION">
      <H3TextSm2 />
      <DivGrid />
    </div>
  );
}

function Component8() {
  return (
    <div className="relative shrink-0 size-[16px]" data-name="Component 1">
      <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 16 16">
        <g id="Component 1">
          <path d={svgPaths.p2949e900} id="Vector" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d={svgPaths.p22e64900} id="Vector_2" stroke="var(--stroke-0, #06B6D4)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
        </g>
      </svg>
    </div>
  );
}

function H3TextSm3() {
  return (
    <div className="content-stretch flex gap-[8px] items-center relative shrink-0 w-full" data-name="h3.text-sm">
      <Component8 />
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[14px] text-black tracking-[1.4px] uppercase whitespace-nowrap">
        <p className="leading-[20px]">Refund Method</p>
      </div>
    </div>
  );
}

function InputW1() {
  return (
    <div className="bg-white relative rounded-[50px] shrink-0 size-[16px]" data-name="input.w-4">
      <div aria-hidden="true" className="absolute border border-[#06b6d4] border-solid inset-0 pointer-events-none rounded-[50px]" />
      <div className="absolute bg-[#06b6d4] left-[3.2px] rounded-[50px] size-[9.6px] top-[3.2px]" data-name="input.w-4:checked" />
    </div>
  );
}

function InputW4Margin2() {
  return (
    <div className="h-[20px] relative shrink-0 w-[16px]" data-name="input.w-4:margin">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-start pt-[4px] relative size-full">
        <InputW1 />
      </div>
    </div>
  );
}

function SpanBlock4() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0" data-name="span.block">
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[13.5px] text-black whitespace-nowrap">
        <p className="leading-[20px]">Store Wallet Credit</p>
      </div>
    </div>
  );
}

function SpanTextXs() {
  return (
    <div className="bg-[#e4e4e7] content-stretch flex flex-col items-start px-[6px] py-[2px] relative rounded-[4px] shrink-0" data-name="span.text-xs">
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[#0891b2] text-[11.3px] whitespace-nowrap">
        <p className="leading-[16px]">Bal: ₱1,250.00</p>
      </div>
    </div>
  );
}

function DivFlex1() {
  return (
    <div className="content-stretch flex gap-[8px] items-center relative shrink-0 w-full" data-name="div.flex">
      <SpanBlock4 />
      <SpanTextXs />
    </div>
  );
}

function SpanBlock5() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0 w-full" data-name="span.block">
      <div className="flex flex-col font-['Inter:Regular',sans-serif] font-normal justify-center leading-[0] not-italic relative shrink-0 text-[#71717a] text-[9.2px] whitespace-nowrap">
        <p className="leading-[15px]">Fastest option. Funds added immediately after approval.</p>
      </div>
    </div>
  );
}

function Div10() {
  return (
    <div className="relative shrink-0" data-name="div">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col gap-[4px] items-start relative">
        <DivFlex1 />
        <SpanBlock5 />
      </div>
    </div>
  );
}

function LabelFlex2() {
  return (
    <div className="bg-[rgba(236,254,255,0.1)] flex-[1_0_0] min-h-px min-w-px relative rounded-[12px] self-stretch" data-name="label.flex">
      <div aria-hidden="true" className="absolute border border-[#06b6d4] border-solid inset-0 pointer-events-none rounded-[12px]" />
      <div className="content-stretch flex gap-[12px] items-start p-[17px] relative size-full">
        <InputW4Margin2 />
        <Div10 />
      </div>
    </div>
  );
}

function InputW4Margin3() {
  return (
    <div className="h-[20px] relative shrink-0 w-[16px]" data-name="input.w-4:margin">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-start pt-[4px] relative size-full">
        <div className="bg-white relative rounded-[50px] shrink-0 size-[16px]" data-name="input.w-4">
          <div aria-hidden="true" className="absolute border border-[#71717a] border-solid inset-0 pointer-events-none rounded-[50px]" />
        </div>
      </div>
    </div>
  );
}

function SpanBlock6() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0 w-full" data-name="span.block">
      <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[13.5px] text-black whitespace-nowrap">
        <p className="leading-[20px]">Original Payment Method</p>
      </div>
    </div>
  );
}

function SpanBlock7() {
  return (
    <div className="content-stretch flex flex-col items-start relative shrink-0 w-full" data-name="span.block">
      <div className="flex flex-col font-['Inter:Regular',sans-serif] font-normal justify-center leading-[0] not-italic relative shrink-0 text-[#71717a] text-[9.2px] whitespace-nowrap">
        <p className="leading-[15px]">Refund to your card/bank. Takes 5-10 business days.</p>
      </div>
    </div>
  );
}

function Div11() {
  return (
    <div className="relative shrink-0" data-name="div">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col gap-[4px] items-start relative">
        <SpanBlock6 />
        <SpanBlock7 />
      </div>
    </div>
  );
}

function LabelFlex3() {
  return (
    <div className="flex-[1_0_0] min-h-px min-w-px relative rounded-[12px] self-stretch" data-name="label.flex">
      <div aria-hidden="true" className="absolute border border-[#e4e4e7] border-solid inset-0 pointer-events-none rounded-[12px]" />
      <div className="content-stretch flex gap-[12px] items-start p-[17px] relative size-full">
        <InputW4Margin3 />
        <Div11 />
      </div>
    </div>
  );
}

function DivGrid1() {
  return (
    <div className="content-stretch flex gap-[16px] items-start justify-center relative shrink-0 w-full" data-name="div.grid">
      <LabelFlex2 />
      <LabelFlex3 />
    </div>
  );
}

function Component5RefundPreferenceRenumbered() {
  return (
    <div className="content-stretch flex flex-col gap-[24px] items-start relative shrink-0 w-full" data-name="5. Refund Preference (Renumbered">
      <H3TextSm3 />
      <DivGrid1 />
    </div>
  );
}

function Component9() {
  return (
    <div className="relative rounded-[12px] shrink-0" data-name="Component 4">
      <div aria-hidden="true" className="absolute border border-[#e4e4e7] border-solid inset-0 pointer-events-none rounded-[12px]" />
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col items-center justify-center px-[33px] py-[17px] relative">
        <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[#71717a] text-[12px] text-center uppercase whitespace-nowrap">
          <p className="leading-[16px]">Cancel</p>
        </div>
      </div>
    </div>
  );
}

function Component11() {
  return (
    <div className="relative shrink-0 size-[16px]" data-name="Component 1">
      <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 16 16">
        <g id="Component 1">
          <path d="M3.33333 8H12.6667" id="Vector" stroke="var(--stroke-0, white)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
          <path d={svgPaths.p1d405500} id="Vector_2" stroke="var(--stroke-0, white)" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.33333" />
        </g>
      </svg>
    </div>
  );
}

function Component10() {
  return (
    <div className="bg-black flex-[1_0_0] min-h-px min-w-px relative rounded-[12px] shadow-[0px_10px_15px_-3px_rgba(0,0,0,0.1),0px_4px_6px_-4px_rgba(0,0,0,0.1)]" data-name="Component 5">
      <div className="flex flex-row items-center justify-center overflow-clip rounded-[inherit] size-full">
        <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex gap-[8px] items-center justify-center px-[32px] py-[17px] relative w-full">
          <div className="flex flex-col font-['Inter:Bold',sans-serif] font-bold justify-center leading-[0] not-italic relative shrink-0 text-[12px] text-center text-white tracking-[1.2px] uppercase whitespace-nowrap">
            <p className="leading-[16px]">Submit Request</p>
          </div>
          <Component11 />
        </div>
      </div>
    </div>
  );
}

function Actions() {
  return (
    <div className="content-stretch flex gap-[16px] items-start pt-[25px] relative shrink-0 w-full" data-name="Actions">
      <div aria-hidden="true" className="absolute border-[#f4f4f5] border-solid border-t inset-0 pointer-events-none" />
      <Component9 />
      <Component10 />
    </div>
  );
}

function FormSpaceY() {
  return (
    <div className="relative shrink-0 w-[782px]" data-name="form.space-y-10">
      <div className="bg-clip-padding border-0 border-[transparent] border-solid content-stretch flex flex-col gap-[40px] items-start relative w-full">
        <Component1ContactOrderInfoReadOnly />
        <Component2SelectItems />
        <Component3ReturnDetails />
        <Component4ReturnMethodNewSection />
        <Component5RefundPreferenceRenumbered />
        <Actions />
      </div>
    </div>
  );
}

function DivBgWhite() {
  return (
    <div className="bg-white relative rounded-[12px] shrink-0 w-full" data-name="div.bg-white">
      <div className="overflow-clip rounded-[inherit] size-full">
        <div className="content-stretch flex flex-col items-start pb-[49px] pt-[41px] px-[33px] relative w-full">
          <FormSpaceY />
        </div>
      </div>
      <div aria-hidden="true" className="absolute border border-[#e4e4e7] border-solid inset-0 pointer-events-none rounded-[12px] shadow-[0px_4px_6px_-1px_rgba(0,0,0,0.05),0px_2px_4px_-1px_rgba(0,0,0,0.03)]" />
    </div>
  );
}

function DivContainer() {
  return (
    <div className="max-w-[896px] relative shrink-0 w-full" data-name="div.container">
      <div className="content-stretch flex flex-col gap-[32px] items-start max-w-[inherit] px-[24px] relative w-full">
        <BackButton />
        <DivFlex />
        <DivBgWhite />
      </div>
    </div>
  );
}

export default function MainContent() {
  return (
    <div className="content-stretch flex flex-col items-start pb-[80px] pt-[112px] px-[512px] relative size-full" data-name="MAIN CONTENT">
      <DivContainer />
    </div>
  );
}