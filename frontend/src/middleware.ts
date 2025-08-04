import { NextResponse, NextRequest } from "next/server";

export const middleware = (request: NextRequest) => {
    if (!request.nextUrl.pathname.includes('.') && request.nextUrl.pathname === '/email_verify') {
        console.log('ミドルウェア適用');
        // return NextResponse.redirect(new URL("/login", request.url));
    }

    return NextResponse.next();
};

export const config = {
    matcher: [
        '/email_verify',
    ]
};