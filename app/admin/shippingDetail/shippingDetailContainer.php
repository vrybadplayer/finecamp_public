                        <div class="infoContainer">
                            <div class="recipientInfoContainer">
                                <div class="recipientInfoHeaderContainer">
                                    <img src="../../images/member/shippingDetail/recipient_icon.png" alt="Recipient Icon" />
                                    <div class="recipientInfoHeader">Recipient Information</div>
                                </div>
                                <div class="recipientInfoFieldContainer">
                                    <div class="recipientNameContainer">
                                        <div class="recipientNameTitle">Recipient Name</div>
                                        <?php  html_text('recipientName', 'placeholder="Recipient Name" minlength="1" maxlength="100"') ?>
                                        <?= err('recipientName') ?>
                                    </div>
                                    <div class="recipientPhoneNoContainer">
                                        <div class="recipientPhoneNoTitle">Recipient Phone Number</div>
                                        <div class="recipientCountryNPhoneNo">
                                            <div class="phoneCode">+60</div>
                                            <?php  html_text('recipientPhoneNo', 'placeholder="E.g. 12-3456789" minlength="10" maxlength="11"') ?>
                                        </div>
                                        <?= err('recipientPhoneNo') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="shippingAddressContainer">
                                <div class="shippingAddressHeaderContainer">
                                    <img src="../../images/member/shippingDetail/address_icon.png" alt="Address Icon" />
                                    <div class="shippingAddressHeader">Shipping Address</div>
                                </div>
                                <div class="shippingAddressFieldContainer">
                                    <div class="addressLineContainer">
                                        <div class="addressLineTitle">Address Line</div>
                                        <?php  html_text('addressLine', 'placeholder="Address Line" minlength="1" maxlength="100"') ?>
                                        <?= err('addressLine') ?>
                                    </div>
                                    <div class="addressDetailContainer">
                                        <div class="postcodeContainer">
                                            <div class="postcodeTitle">Postcode</div>
                                            <?php  html_text('postcode', 'placeholder="Postcode" minlength="5" maxlength="5"') ?>
                                            <?= err('postcode') ?>
                                        </div>
                                        <div class="cityContainer">
                                            <div class="cityTitle">City</div>
                                            <?php  html_text('city', 'placeholder="City" minlength="1" maxlength="100"') ?>
                                            <?= err('city') ?>
                                        </div>
                                        <div class="stateContainer">
                                            <div class="stateTitle">State</div>
                                            <?php html_select('state', $_state, null, 'placeholder="Select State"') ?>
                                            <?= err('state') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>