<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
	<head>
	 	<!--[if gte mso 9]>
	 	<xml>
	 		<o:OfficeDocumentSettings>
	 		<o:AllowPNG/>
	 		<o:PixelsPerInch>96</o:PixelsPerInch>
	 		</o:OfficeDocumentSettings>
	 	</xml>
	 	<![endif]-->
	 	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	 	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
	 	<meta name="format-detection" content="date=no" />
	 	<meta name="format-detection" content="address=no" />
	 	<meta name="format-detection" content="telephone=no" />
	 	<meta name="x-apple-disable-message-reformatting" />
	 	<title>Accreditation</title>
	 	<!--[if gte mso 9]>
	 	<style type="text/css" media="all">
	 		sup { font-size: 100% !important; }
	 	</style>
	 	<![endif]-->


		<style type="text/css" media="all">
			/* Linked Styles */
			body { padding:0 !important; margin:0 auto !important; display:block !important; min-width:100% !important; width:100% !important; background:#f3f3f3; -webkit-text-size-adjust:none }
			a { color:#21252f; text-decoration:none }
			p { padding: 20px 0 !important; margin:0 !important }
			img { -ms-interpolation-mode: bicubic; /* Allow smoother rendering of resized image in Internet Explorer */ }

			h2,
			h3,
			h4,
			h5,
			p,
			span { font-family:'Avenir', Helvetica, Arial, sans-serif; }

			h2 { padding: 20px 0 26px; margin: 0; font-size: 48px; font-weight: 600; text-transform: uppercase; }
			h3 { margin: 0; color: #0467cf; font-size: 46px; line-height: 52px; font-weight: 500; }
			h4 { color: #323232; margin: 20px 0; font-size: 30px; line-height: 36px; font-weight: bold; }
			h5 { margin: 32px 0; color: #848484; font-weight: bold; font-size: 22px; line-height: 26px; }

			.wrapper { background: url('images/accreditation/background.png') no-repeat 0 0; background-size: cover; display: flex; flex-direction: column; height: 100vh; }
			.header { padding: 26px 26px 0; background: #f2f2f2; }
			.header-inner { width: 100%; height: 100%; border-top: 4px solid #ffffff; border-left: 4px solid #ffffff; border-right: 4px solid #fff; }
			.header-inner img { margin-top: -30px; }
			.content { display: flex; flex-direction: column; justify-content: center; flex: 1; }
			.images { display: flex; align-items: center; justify-content: space-between; padding: 0 40px; }
			.accredited-image { position: relative; }
			.accredited-image span { display: block; position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); font-size: 9px; font-weight: bold; }
			.bottom-text { padding: 0!important; margin: -10px 0 25px!important; color: #777777; font-size: 16px; line-height: 18px; font-weight: bold; }
			.footer { display: flex;  background: #f2f2f2; justify-content: center; }
			.footer-inner { background: #0467cf; padding: 10px 22px; }
			.footer-inner p { padding: 0!important; color: #fff; font-size: 11px; }
		</style>
	</head>
	<body class="body" style="padding:0 !important; margin:0 auto !important; display:block !important; min-width:100% !important; width:100% !important; background:#fff; -webkit-text-size-adjust:none;">
		<center>
			<div class="wrapper">
				<div class="header">
					<div class="header-inner">
						<img src="{{ asset('images/accreditation/logo.png') }}" width="234" height="150" border="0" alt="" />
						<h2>Certificate of training</h2>
					</div>
				</div>

				<div class="content">
					<h5>THIS ACKNOWLEDGES THAT</h5>
					<h3>{{ $name }}</h3>
					<h5>HAS SUCCESSFULLY COMPLETED THE TRAINING & TESTING FOR</h5>
					<h4>BLUON TIER I ACCREDITATION</h4>

					<div class="images">
						<div class="accredited-image">
							<img src="{{ asset('images/accreditation/accredited-icon.png') }}" width="136" height="126" border="0" alt="" />
							<span>{{ $date }}</span>
						</div>

						<img src="{{ asset('images/accreditation/signature.png') }}" width="162" height="74" border="0" alt="" style="margin-left: 70px;" />

						<img src="{{ asset('images/accreditation/tdx-logo.png') }}" width="174" height="62" border="0" alt="" />
					</div>

					<p class="bottom-text">PETER CAPUCIATI | CHAIRMAN & CEO</p>
				</div>

				<div class="footer">
					<div class="footer-inner">
						<p>855.425.8686 | www.bluonenergy.com</p>
					</div>
				</div>
			</div>
		</center>
	</body>
</html>
