<?php

echo "
    <!DOCTYPE html>
<html lang='en'>
  <head>
    <meta charset='UTF-8' />
    <title>Email Template</title>
  </head>
  <body style='margin:0; padding:0; background-color:#f5f5f5;'>
    <table width='100%' cellpadding='0' cellspacing='0' border='0'>
      <tr>
        <td align='center' style='padding: 20px 0;'>
          <table width='600' cellpadding='0' cellspacing='0' border='0' style='background-color: #ffffff; border-radius: 10px; overflow: hidden;'>
            <tr>
              <td style='background-color: cornflowerblue; padding: 20px; text-align: center;'>
                <h1 style='margin: 0; color: white; font-family: Arial, sans-serif; font-size: 24px;'>
                  {{ TITLE }}
                </h1>
              </td>
            </tr>
            <tr>
              <td style='padding: 20px; font-family: Arial, sans-serif; color: black; font-size: 16px; line-height: 1.5;'>
                {{ CONTENT }}
              </td>
            </tr>
            <tr>
              <td style='padding: 0 20px 20px 20px; font-family: Arial, sans-serif;'>
                <p style='margin: 0; color: #999999; font-size: 14px;'>
                  Sincerely,<br />
                  The dentists team
                </p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>

";