import "./globals.scss";
import FlashToaster from "@/components/ui/toaster/FlashToaster";

const RootLayout = ({
	children,
}: Readonly<{
	children: React.ReactNode;
}>) => {
	return (
		<html lang="ja">
			<body>
				<FlashToaster />
				{children}
			</body>
		</html>
	);
};

export default RootLayout;